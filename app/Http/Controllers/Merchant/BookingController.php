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
use Illuminate\Support\Facades\Validator;

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

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staffs,id',
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'date_time' => 'required|date_format:Y-m-d H:i:s',
            'special_note' => 'nullable|string|max:255',
            'payment_method' => 'nullable|in:0,1,2,3',
            'amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $staff = Staff::where('id', $request->staff_id)
            ->where('user_id', $merchant->id)
            ->where('status', 1)
            ->first();

        $service = Service::where('id', $request->service_id)
            ->where('user_id', $merchant->id)
            ->first();

        if (! $staff || ! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid staff or service selection',
            ], 403);
        }

        DB::transaction(function () use ($request, $merchant, $service) {

            $booking = Booking::create([
                'user_id' => $merchant->id,
                'staff_id' => $request->staff_id,
                'service_id' => $service->id,
                'customer_name' => $request->customer_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_time' => $request->date_time,
                'status' => 'pending',
                'special_note' => $request->special_note,
                'booking_by' => 'merchant',
                'payment_method' => $request->payment_method ?? 2,
            ]);

            if ($request->has('payment_method')) {
                MerchantPayment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $merchant->id,
                    'payment_method' => $request->payment_method,
                    'amount' => $request->amount ?? $service->price,
                    'payment_status' => 'Due',
                    'transaction_id' => $request->transaction_id ?? null,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
        ], 201);
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
                ->whereIn('status', ['pending', 'confirm'])
                ->get();
        } else {
            $bookings = Booking::whereIn('staff_id', $staffIds)
                ->where('service_id', $service->id)
                ->whereDate('date_time', $date)
                ->whereIn('status', ['pending', 'confirm'])
                ->get();
        }

        $availableSlots = [];
        $now = Carbon::now($merchantTimeZone);

        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($request->date.' '.$slot, $merchantTimeZone);
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

        $slotStart = Carbon::parse($request->date.' '.$request->time);
        $slotEnd = $slotStart->copy()->addMinutes($duration);

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
            ->whereIn('status', ['pending', 'confirm'])
            ->get();

        $bookedStaff = [];
        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->date_time);
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
            'special_note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {

            $service = Service::findOrFail($request->service_id);
            $merchantId = $service->user_id;
            $duration = (int) $service->duration;

            $slotStart = Carbon::parse($request->date.' '.$request->time);
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
                    ->whereIn('status', ['pending', 'confirm'])
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
                        ->whereIn('status', ['pending', 'confirm'])
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
                'booking_by' => 'user',
                'payment_method' => 2,
            ]);

            MerchantPayment::create([
                'booking_id' => $booking->id,
                'user_id' => $merchantId,
                'payment_method' => 2,
                'amount' => $service->price,
                'transaction_id' => 'PAY-STORE-'.uniqid(),
                'payment_status' => 'Due',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed. Pay at store.',
                'booking_id' => $booking->id,
                'staff_id' => $staffId,
            ], 201);
        });
    }
}
