<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\MerchantPayment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $bookings = Booking::with(['user', 'staff', 'service'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No bookings found for this user',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bookings retrieved successfully',
            'data' => $bookings,
        ], 200);
    }

    public function show($id)
    {
        $userId = auth()->id();

        $booking = Booking::with(['user', 'staff', 'service'])
            ->where('user_id', $userId)
            ->where('id', $id)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => true,
                'message' => 'Booking not found for this user',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking retrieved successfully',
            'data' => $booking,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $userId = auth()->id();

        $request->validate([
            'status' => 'required|in:pending,confirm,complete,cancel',
            'payment_status' => 'required|in:Due,paid',
        ]);

        $booking = Booking::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found for this user',
            ], 404);
        }

        $booking->status = $request->status;
        $booking->save();

        $payment = MerchantPayment::where('booking_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($payment) {
            $payment->payment_status = $request->payment_status;
            $payment->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking and payment status updated successfully',
            'data' => [
                'booking' => $booking,
                'payment' => $payment,
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $merchant = auth()->user();

        $request->validate([
            'service_id'    => 'required|exists:services,id',
            'staff_id'      => 'nullable|integer',
            'date'          => 'required|date',
            'time'          => 'required',
            'customer_name' => 'required|string',
            'email'         => 'required|email',
            'phone'         => 'required|string',
            'special_note'  => 'nullable|string',
            'payment_method' => 'required|in:tap,cash',
        ]);

        return DB::transaction(function () use ($request, $merchant) {

            $service = Service::where('id', $request->service_id)
                ->where('user_id', $merchant->id)
                ->first();

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid service selection'
                ], 422);
            }

            $storeSetting = DB::table('merchant_store_settings')
                ->where('user_id', $merchant->id)
                ->first();

            if (! $storeSetting || ! $storeSetting->time_zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store timezone not set'
                ], 422);
            }

            $merchantTimeZone = $storeSetting->time_zone;

            $duration = (int) $service->duration;

            $date = Carbon::parse($request->date, $merchantTimeZone)->startOfDay();
            $today = Carbon::now($merchantTimeZone)->startOfDay();

            if ($date->lt($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected date is in the past'
                ], 422);
            }

            $day = strtolower($date->format('l'));

            $businessHour = BusinessHour::where('merchant_store_setting_id', $storeSetting->id)
                ->where('day', $day)
                ->where('is_closed', 0)
                ->first();

            if (! $businessHour || ! $businessHour->open_time || ! $businessHour->close_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid slot selected'
                ], 422);
            }

            $validSlots = [];

            $start = Carbon::createFromTimeString($businessHour->open_time, $merchantTimeZone);
            $end = Carbon::createFromTimeString($businessHour->close_time, $merchantTimeZone);

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $validSlots[] = $start->format('H:i');
                $start->addMinutes($duration);
            }

            $selectedTime = Carbon::parse($request->time)->format('H:i');

            if (! in_array($selectedTime, $validSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid slot selected'
                ], 422);
            }

            $slotStart = Carbon::parse($request->date . ' ' . $request->time, $merchantTimeZone);
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            $now = Carbon::now($merchantTimeZone);
            if ($slotStart->lt($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected time is in the past'
                ], 422);
            }

            $staff = Staff::where('id', $request->staff_id)
                ->where('user_id', $merchant->id)
                ->where('service_id', $service->id)
                ->where('status', 1)
                ->first();

            if ($request->staff_id) {

                $staff = Staff::where('id', $request->staff_id)
                    ->where('user_id', $merchant->id)
                    ->where('service_id', $service->id)
                    ->where('status', 1)
                    ->first();

                if (!$staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid staff selection'
                    ], 422);
                }

                $conflict = Booking::where('staff_id', $staff->id)
                    ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                    ->where(function ($q) use ($slotStart, $slotEnd) {
                        $q->where('date_time', '<', $slotEnd)
                            ->whereRaw(
                                "DATE_ADD(date_time, INTERVAL (SELECT duration FROM services WHERE services.id = bookings.service_id) MINUTE) > ?",
                                [$slotStart]
                            );
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This staff is not available at this time.'
                    ], 409);
                }

                $staffId = $staff->id;
            } else {

                $staffs = Staff::where('user_id', $merchant->id)
                    ->where('service_id', $service->id)
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->get();

                $freeStaff = null;

                foreach ($staffs as $staff) {

                    $conflict = Booking::where('staff_id', $staff->id)
                        ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                        ->where(function ($q) use ($slotStart, $slotEnd) {
                            $q->where('date_time', '<', $slotEnd)
                                ->whereRaw(
                                    "DATE_ADD(date_time, INTERVAL (SELECT duration FROM services WHERE services.id = bookings.service_id) MINUTE) > ?",
                                    [$slotStart]
                                );
                        })
                        ->exists();

                    if (!$conflict) {
                        $freeStaff = $staff;
                        break;
                    }
                }

                if (!$freeStaff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No staff available at this time slot.'
                    ], 409);
                }

                $staffId = $freeStaff->id;
            }

            $booking = Booking::create([
                'user_id'        => $merchant->id,
                'staff_id'       => $staffId,
                'service_id'     => $service->id,
                'customer_name'  => $request->customer_name,
                'email'          => $request->email,
                'phone'          => $request->phone,
                'date_time'      => $slotStart,
                'status'         => 'pending',
                'special_note'   => $request->special_note,
                'booking_by'     => 'merchant',
            ]);

            $merchantPayment = MerchantPayment::create([
                'booking_id'     => $booking->id,
                'user_id'        => $merchant->id,
                'payment_method' => $request->payment_method,
                'amount'         => $service->price,
                'transaction_id' => $request->payment_method === 'cash'
                    ? 'cash-' . uniqid()
                    : null,
                'payment_status' => 'due',
            ]);

            if ($request->payment_method === 'tap') {

                $tapSetting = DB::table('tap_payments')
                    ->where('user_id', $merchant->id)
                    ->latest('updated_at')
                    ->first();

                if (! $tapSetting || ! $tapSetting->tap_secret_key) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tap Payment details not found for this merchant.',
                    ], 422);
                }

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ])->post('https://api.tap.company/v2/charges', [
                    'amount' => $service->price,
                    'currency' => 'KWD',
                    'customer' => [
                        'first_name' => $request->customer_name,
                        'email' => $request->email,
                        'phone' => [
                            'country_code' => '965',
                            'number' => $request->phone,
                        ],
                    ],
                    'source' => ['id' => 'src_all'],
                    'redirect' => ['url' => url('/api/payment/callback')],
                    'metadata' => [
                        'booking_id' => $booking->id,
                    ],
                ]);

                $resData = $response->json();

                if ($response->successful() && isset($resData['transaction']['url'])) {

                    $merchantPayment->update([
                        'transaction_id' => $resData['id'] ?? null,
                    ]);

                    return response()->json([
                        'success' => true,
                        'payment_url' => $resData['transaction']['url'],
                        'booking_id' => $booking->id,
                    ], 200);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Tap API Error: ' . ($resData['errors'][0]['description'] ?? 'Transaction failed'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking_id' => $booking->id,
            ], 201);
        });
    }

    public function paymentCallback(Request $request)
    {

        $tapTransactionId = $request->query('tap_id');

        if (! $tapTransactionId) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID missing',
            ], 400);
        }

        try {

            $payment = MerchantPayment::where('transaction_id', $tapTransactionId)->first();

            if (! $payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found',
                ], 404);
            }

            $tapSetting = DB::table('tap_payments')
                ->where('user_id', $payment->user_id)
                ->latest('updated_at')
                ->first();

            if (! $tapSetting || ! $tapSetting->tap_secret_key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tap Payment settings not found',
                ], 422);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
                'Accept' => 'application/json',
            ])->get("https://api.tap.company/v2/charges/{$tapTransactionId}");

            $resData = $response->json();

            if (! $response->successful() || ! isset($resData['status'])) {
                Log::error('Tap API failed', ['response' => $resData]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify payment with Tap.',
                ], 400);
            }

            $status = $resData['status'];

            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already processed',
                    'booking_id' => $payment->booking_id,
                ]);
            }

            if ($status === 'CAPTURED') {
                $payment->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                $booking = Booking::find($payment->booking_id);
                if ($booking) {
                    $booking->update([
                        'status' => 'confirm',
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'booking_id' => $payment->booking_id,
                ]);
            } else {
                $payment->update([
                    'payment_status' => 'failed',
                ]);

                $booking = Booking::find($payment->booking_id);
                if ($booking) {
                    $booking->update([
                        'status' => 'cancelled',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed or pending',
                    'booking_id' => $payment->booking_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Tap callback error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Server error during payment verification',
            ], 500);
        }
    }

    public function getAvailability(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'staff_id' => 'nullable|integer',
        ]);

        $service = Service::find($request->service_id);
        if (! $service) {
            return response()->json(['available_times' => [], 'message' => 'Service not found'], 404);
        }

        $merchantId = $service->user_id;

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $merchantId)
            ->first();

        if (! $storeSetting || ! $storeSetting->time_zone) {
            return response()->json(['available_times' => [], 'message' => 'Store timezone not set']);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $date = Carbon::parse($request->date, $merchantTimeZone);
        $day = strtolower($date->format('l'));

        $today = Carbon::now($merchantTimeZone)->startOfDay();
        if ($date->lt($today)) {
            return response()->json(['available_times' => [], 'message' => 'Selected date is in the past']);
        }

        $businessHour = BusinessHour::where('merchant_store_setting_id', $storeSetting->id)
            ->where('day', $day)
            ->where('is_closed', 0)
            ->first();

        if (! $businessHour) {
            return response()->json(['available_times' => [], 'message' => 'Business closed']);
        }

        if (!$businessHour->open_time || !$businessHour->close_time) {
            return response()->json([
                'available_times' => [],
                'message' => 'Business hours not properly set'
            ]);
        }

        $staffIds = Staff::where('service_id', $service->id)
            ->where('user_id', $merchantId)
            ->where('status', 1)
            ->pluck('id');

        if ($staffIds->isEmpty()) {
            return response()->json(['available_times' => [], 'message' => 'No staff available for this service'], 404);
        }

        $duration = (int) $service->duration;

        $slots = [];
        $start = Carbon::createFromTimeString($businessHour->open_time, $merchantTimeZone);
        $end = Carbon::createFromTimeString($businessHour->close_time, $merchantTimeZone);

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($duration);
        }

        if ($request->staff_id) {
            $bookings = Booking::where('staff_id', $request->staff_id)
                ->where('service_id', $service->id)
                ->whereDate('date_time', $date)
                ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                ->get();
        } else {
            $bookings = Booking::whereIn('staff_id', $staffIds)
                ->where('service_id', $service->id)
                ->whereDate('date_time', $date)
                ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                ->get();
        }

        $availableSlots = [];
        $now = Carbon::now($merchantTimeZone);

        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($request->date . ' ' . $slot, $merchantTimeZone);
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            if ($date->isToday() && $slotStart->lte($now)) {
                continue;
            }

            $overlapStaff = [];
            foreach ($bookings as $booking) {
                $bookingStart = Carbon::parse($booking->date_time, $merchantTimeZone);
                $bookingEnd = $bookingStart->copy()->addMinutes($booking->service->duration);

                if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                    $overlapStaff[$booking->staff_id] = true;
                }
            }

            if (count($overlapStaff) < count($staffIds)) {
                $availableSlots[] = $slotStart->format('h:i A');
            }
        }

        return response()->json(['available_times' => $availableSlots]);
    }

    public function getAvailableStaffByTime(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'time' => 'required',
        ]);

        $service = Service::findOrFail($request->service_id);
        $merchantId = $service->user_id;
        $duration = (int) $service->duration;

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $merchantId)
            ->first();

        if (! $storeSetting || ! $storeSetting->time_zone) {
            return response()->json([
                'available_staff' => [],
                'message' => 'Store timezone not set'
            ]);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $date = Carbon::parse($request->date, $merchantTimeZone)->startOfDay();
        $today = Carbon::now($merchantTimeZone)->startOfDay();

        if ($date->lt($today)) {
            return response()->json([
                'available_staff' => [],
                'message' => 'Selected date is in the past'
            ]);
        }

        $day = strtolower($date->format('l'));

        $businessHour = BusinessHour::where('merchant_store_setting_id', $storeSetting->id)
            ->where('day', $day)
            ->where('is_closed', 0)
            ->first();

        if (! $businessHour) {
            return response()->json([
                'available_staff' => [],
                'message' => 'Invalid slot selected'
            ]);
        }

        $validSlots = [];
        $start = Carbon::createFromTimeString($businessHour->open_time, $merchantTimeZone);
        $end = Carbon::createFromTimeString($businessHour->close_time, $merchantTimeZone);

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $validSlots[] = $start->format('H:i');
            $start->addMinutes($duration);
        }

        $selectedTime = Carbon::parse($request->time)->format('H:i');

        if (! in_array($selectedTime, $validSlots)) {
            return response()->json([
                'available_staff' => [],
                'message' => 'Invalid slot selected'
            ], 422);
        }

        $slotStart = Carbon::parse($request->date . ' ' . $request->time, $merchantTimeZone);
        $slotEnd = $slotStart->copy()->addMinutes($duration);

        $now = Carbon::now($merchantTimeZone);
        if ($slotStart->lt($now)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time is in the past'
            ], 422);
        }

        $staffIds = Staff::where('user_id', $merchantId)
            ->where('service_id', $service->id)
            ->where('status', 1)
            ->pluck('id');

        if ($staffIds->isEmpty()) {
            return response()->json([
                'available_staff' => [],
                'message' => 'No staff available for this service',
            ], 404);
        }

        $bookings = Booking::whereIn('staff_id', $staffIds)
            ->where('service_id', $service->id)
            ->whereDate('date_time', $request->date)
            ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
            ->get();

        $bookedStaff = [];
        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->date_time, $merchantTimeZone);
            $bookingEnd = $bookingStart->copy()->addMinutes($booking->service->duration);

            if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                $bookedStaff[$booking->staff_id] = true;
            }
        }

        $availableStaff = Staff::whereIn('id', $staffIds->diff(array_keys($bookedStaff)))->get();

        return response()->json([
            'available_staff' => $availableStaff,
        ]);
    }


    public function bookingByUser(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|integer',
            'date' => 'required|date',
            'time' => 'required',
            'customer_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'payment_method' => 'required|in:cash,tap',
            'special_note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {

            $service = Service::findOrFail($request->service_id);
            $merchantId = $service->user_id;

            $storeSetting = DB::table('merchant_store_settings')
                ->where('user_id', $merchantId)
                ->first();

            if (! $storeSetting || ! $storeSetting->time_zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store timezone not set'
                ], 422);
            }

            $merchantTimeZone = $storeSetting->time_zone;

            $duration = (int) $service->duration;

            $newDate = Carbon::parse($request->date, $merchantTimeZone)->startOfDay();
            $today   = Carbon::now($merchantTimeZone)->startOfDay();

            if ($newDate->lt($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected date is in the past.'
                ], 400);
            }

            $slotStart = Carbon::parse($request->date . ' ' . $request->time, $merchantTimeZone);
            $now = Carbon::now($merchantTimeZone);

            if ($slotStart->lte($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected time is in the past.'
                ], 400);
            }

            $day = strtolower($slotStart->format('l'));

            $businessHour = BusinessHour::where('merchant_store_setting_id', $storeSetting->id)
                ->where('day', $day)
                ->where('is_closed', 0)
                ->first();

            if (! $businessHour || ! $businessHour->open_time || ! $businessHour->close_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid slot selected.'
                ], 422);
            }

            $validSlots = [];

            $start = Carbon::createFromTimeString($businessHour->open_time, $merchantTimeZone);
            $end   = Carbon::createFromTimeString($businessHour->close_time, $merchantTimeZone);

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $validSlots[] = $start->format('H:i');
                $start->addMinutes($duration);
            }

            $selectedTime = $slotStart->format('H:i');

            if (! in_array($selectedTime, $validSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid slot selected.'
                ], 422);
            }

            $slotEnd = $slotStart->copy()->addMinutes($duration);

            if ($request->staff_id) {

                $staff = Staff::where('id', $request->staff_id)
                    ->where('user_id', $merchantId)
                    ->where('service_id', $service->id)
                    ->where('status', 1)
                    ->first();

                if (! $staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid staff selection',
                    ], 422);
                }

                $conflict = Booking::where('staff_id', $staff->id)
                    ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                    ->where(function ($q) use ($slotStart, $slotEnd) {
                        $q->where('date_time', '<', $slotEnd)
                            ->whereRaw(
                                'DATE_ADD(date_time, INTERVAL (SELECT duration FROM services WHERE services.id = bookings.service_id) MINUTE) > ?',
                                [$slotStart]
                            );
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This staff is not available at this time.',
                    ], 409);
                }

                $staffId = $staff->id;
            } else {

                $staffs = Staff::where('user_id', $merchantId)
                    ->where('service_id', $service->id)
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->get();

                $freeStaff = null;

                foreach ($staffs as $staff) {
                    $conflict = Booking::where('staff_id', $staff->id)
                        ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                        ->where(function ($q) use ($slotStart, $slotEnd) {
                            $q->where('date_time', '<', $slotEnd)
                                ->whereRaw(
                                    'DATE_ADD(date_time, INTERVAL (SELECT duration FROM services WHERE services.id = bookings.service_id) MINUTE) > ?',
                                    [$slotStart]
                                );
                        })
                        ->exists();

                    if (! $conflict) {
                        $freeStaff = $staff;
                        break;
                    }
                }

                if (! $freeStaff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No staff available at this time slot.',
                    ], 409);
                }

                $staffId = $freeStaff->id;
            }

            $booking = Booking::create([
                'user_id' => $merchantId,
                'staff_id' => $staffId,
                'service_id' => $service->id,
                'customer_name' => $request->customer_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_time' => $slotStart,
                'status' => 'pending',
                'special_note' => $request->special_note,
                'booking_by' => auth()->id(),
            ]);

            $payment = MerchantPayment::create([
                'booking_id' => $booking->id,
                'user_id' => $merchantId,
                'payment_method' => $request->payment_method,
                'amount' => $service->price,
                'transaction_id' => 'tx' . uniqid(),
                'payment_status' => 'due',
            ]);

            if ($request->payment_method == 'cash') {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking confirmed (Cash).',
                    'booking_id' => $booking->id,
                    'staff_id' => $staffId,
                ], 201);
            }

            if ($request->payment_method == 'tap') {

                $tapPayment = DB::table('tap_payments')
                    ->where('user_id', $merchantId)
                    ->latest('updated_at')
                    ->first();

                if (! $tapPayment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tap payment credentials not found for this merchant',
                    ], 422);
                }

                $tapBaseUrl = $tapPayment->tap_mode == 'test'
                    ? 'https://api.tap.company/v2'
                    : 'https://api.tap.company/v2';

                $tapResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $tapPayment->tap_secret_key,
                    'Content-Type' => 'application/json',
                ])->post($tapBaseUrl . '/charges', [

                    'amount' => $service->price,
                    'currency' => 'SAR',

                    'customer' => [
                        'first_name' => $request->customer_name,
                        'email' => $request->email,
                        'phone' => [
                            'country_code' => '966',
                            'number' => $request->phone,
                        ],
                    ],

                    'source' => [
                        'id' => 'src_all',
                    ],

                    'redirect' => [
                        'url' => url('/api/tap-success?booking_id=' . $booking->id),
                    ],
                ]);

                if ($tapResponse->failed()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tap payment creation failed',
                        'error' => $tapResponse->body(),
                    ], 500);
                }

                $tapData = $tapResponse->json();

                $payment->update([
                    'transaction_id' => $tapData['id'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Redirect to Tap payment',
                    'payment_url' => $tapData['transaction']['url'],
                    'booking_id' => $booking->id,
                ], 200);
            }
        });
    }

    public function tapCallbackbooking(Request $request)
    {
        $bookingId = $request->booking_id;
        $tapChargeId = $request->tap_id ?? $request->charge_id ?? null;

        if (! $bookingId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid callback data',
            ], 400);
        }

        $payment = MerchantPayment::where('booking_id', $bookingId)->first();

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found',
            ], 404);
        }

        $merchantId = $payment->user_id;

        $tapPayment = DB::table('tap_payments')
            ->where('user_id', $merchantId)
            ->latest('updated_at')
            ->first();

        if (! $tapPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Tap credentials not found',
            ], 422);
        }

        $tapBaseUrl = 'https://api.tap.company/v2';

        $tapResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $tapPayment->tap_secret_key,
        ])->get($tapBaseUrl . '/charges/' . $payment->transaction_id);

        if ($tapResponse->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify Tap payment',
                'error' => $tapResponse->body(),
            ], 500);
        }

        $tapData = $tapResponse->json();

        DB::transaction(function () use ($tapData, $payment, $bookingId) {

            if ($tapData['status'] == 'CAPTURED') {

                $payment->update([
                    'payment_status' => 'paid',
                ]);

                Booking::where('id', $bookingId)->update([
                    'status' => 'confirm',
                ]);
            } else {

                $payment->update([
                    'payment_status' => 'failed',
                ]);

                Booking::where('id', $bookingId)->update([
                    'status' => 'cancel',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'payment_status' => $tapData['status'],
            'booking_id' => $bookingId,
        ]);
    }
}
