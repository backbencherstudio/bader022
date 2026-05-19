<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Http, Log};
use App\Http\Controllers\Controller;
use App\Models\{Booking, BusinessHour, MerchantPayment, Service, Staff, User,Subscription};
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingCreateMail;
use App\Mail\MerchantBookingNotificationMail;

class BookingController extends Controller
{



    public function index(Request $request)
    {
        $userId = auth()->id();

        $branchId = $request->header('X-Branch-Id');

        if (empty($branchId)) {
            return response()->json([
                'success' => false,
                'message' => 'Branch ID is required in the headers (X-Branch-Id) to see bookings.',
            ], 422);
        }

        $bookings = Booking::with(['user', 'staff', 'service'])
            ->where('user_id', $userId)
            ->where('branch_id', $branchId)
            ->latest()
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No bookings found for this branch',
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
            if ($request->payment_status === 'paid') {
                $payment->paid_at = now();
            }
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

    // public function store(Request $request)
    // {
    //     $merchant = auth()->user();
    //     $branchId = $request->header('X-Branch-Id');

    //     $request->validate([
    //         'service_id'    => 'required|exists:services,id',
    //         'staff_id'      => 'nullable|integer',
    //         'branch_id'      => 'nullable|integer',
    //         'date'          => 'required|date',
    //         'time'          => 'required',
    //         'customer_name' => 'required|string',
    //         'email'         => 'nullable|email',
    //         'phone'         => 'nullable|string',
    //         'special_note'  => 'nullable|string',
    //         'payment_method' => 'required|in:tap,cash',
    //     ]);

    //     $activeBranchId = $branchId ?? $request->branch_id;
    //     // return DB::transaction(function () use ($request, $merchant) {
    //         return DB::transaction(function () use ($request, $merchant, $activeBranchId) {

    //         $userSubscription = Subscription::where('user_id', $merchant->id)->first();

    //     if ($userSubscription && $userSubscription->plan_id == 1) {
    //         $bookingCount = Booking::where('user_id', $merchant->id)->count();

    //         if ($bookingCount >= 25) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Your current plan only allows a maximum of 25 bookings.'
    //             ], 403);
    //         }
    //     }

    //         $service = Service::where('id', $request->service_id)
    //             ->where('user_id', $merchant->id)
    //             ->first();

    //         if (!$service) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid service selection'
    //             ], 422);
    //         }

    //         $storeSetting = DB::table('merchant_store_settings')
    //             ->where('user_id', $merchant->id)
    //             ->first();

    //         if (! $storeSetting || ! $storeSetting->time_zone) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Store timezone not set'
    //             ], 422);
    //         }

    //         $merchantTimeZone = $storeSetting->time_zone;

    //         $duration = (int) $service->duration;

    //         $date = Carbon::parse($request->date, $merchantTimeZone)->startOfDay();
    //         $today = Carbon::now($merchantTimeZone)->startOfDay();

    //         if ($date->lt($today)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Selected date is in the past'
    //             ], 422);
    //         }

    //         $day = strtolower($date->format('l'));

    //         $businessHour = BusinessHour::where('merchant_store_setting_id', $storeSetting->id)
    //             ->where('day', $day)
    //             ->where('is_closed', 0)
    //             ->first();

    //         if (! $businessHour || ! $businessHour->open_time || ! $businessHour->close_time) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid slot selected'
    //             ], 422);
    //         }

    //         $validSlots = [];

    //         $start = Carbon::createFromTimeString($businessHour->open_time, $merchantTimeZone);
    //         $end = Carbon::createFromTimeString($businessHour->close_time, $merchantTimeZone);

    //         while ($start->copy()->addMinutes($duration)->lte($end)) {
    //             $validSlots[] = $start->format('H:i');
    //             $start->addMinutes($duration);
    //         }

    //         $selectedTime = Carbon::parse($request->time)->format('H:i');

    //         if (! in_array($selectedTime, $validSlots)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid slot selected'
    //             ], 422);
    //         }

    //         $slotStart = Carbon::parse($request->date . ' ' . $request->time, $merchantTimeZone);
    //         $slotEnd = $slotStart->copy()->addMinutes($duration);

    //         $now = Carbon::now($merchantTimeZone);
    //         if ($slotStart->lt($now)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Selected time is in the past'
    //             ], 422);
    //         }


    //         $staffQuery = Staff::where('user_id', $merchant->id)
    //             ->whereJsonContains('service_id', (string)$service->id)
    //             ->where('status', 1);

    //         $staffCount = $staffQuery->count();

    //         if ($staffCount == 0) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No staff available for this service'
    //             ], 404);
    //         }

    //         $existingBookings = Booking::whereIn('staff_id', function ($q) use ($merchant, $service) {
    //             $q->select('id')
    //                 ->from('staffs')
    //                 ->where('user_id', $merchant->id)
    //                 ->whereJsonContains('service_id', (string)$service->id)
    //                 ->where('status', 1);
    //         })
    //             ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
    //             ->where(function ($q) use ($slotStart, $slotEnd) {
    //                 $q->where('date_time', '<', $slotEnd)
    //                     ->whereRaw(
    //                         "DATE_ADD(date_time, INTERVAL (SELECT duration FROM services WHERE services.id = bookings.service_id) MINUTE) > ?",
    //                         [$slotStart]
    //                     );
    //             })
    //             ->lockForUpdate()
    //             ->count();

    //         if ($existingBookings >= $staffCount) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'This slot is fully booked.'
    //             ], 409);
    //         }


    //         $staff = $staffQuery->inRandomOrder()->first();

    //         if (!$staff) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No staff available'
    //             ], 404);
    //         }

    //         $staffId = $staff->id;
    //         $branchId = $request->branch_id;

    //         $booking = Booking::create([
    //             'user_id'        => $merchant->id,
    //             'staff_id'       => $staffId,
    //             'service_id'     => $service->id,
    //             'branch_id'      => $activeBranchId,
    //             'customer_name'  => $request->customer_name,
    //             'email'          => $request->email,
    //             'phone'          => $request->phone,
    //             'date_time'      => $slotStart,
    //             'status'         => 'confirm',
    //             'special_note'   => $request->special_note,
    //             'booking_by'     => 'merchant',
    //         ]);

    //         $merchantPayment = MerchantPayment::create([
    //             'booking_id'     => $booking->id,
    //             'user_id'        => $merchant->id,
    //             'branch_id'      => $activeBranchId,
    //             'payment_method' => $request->payment_method,
    //             'amount'         => $service->price,
    //             'transaction_id' => $request->payment_method === 'cash'
    //                 ? 'cash-' . uniqid()
    //                 : null,
    //             'payment_status' => 'paid',
    //             'paid_at' => Carbon::now($merchantTimeZone),
    //         ]);

    //         try {
    //             if ($request->email) {
    //                 Mail::to($request->email)->send(new BookingCreateMail($booking));
    //             }
    //         } catch (\Exception $e) {
    //             Log::error('Booking email failed: ' . $e->getMessage(), [
    //                 'booking_id' => $booking->id,
    //                 'email' => $request->email,
    //             ]);
    //         }

    //         if ($request->payment_method === 'tap') {

    //             $tapSetting = DB::table('tap_payments')
    //                 ->where('user_id', $merchant->id)
    //                 ->latest('updated_at')
    //                 ->first();

    //             if (! $tapSetting || ! $tapSetting->tap_secret_key) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Tap Payment details not found for this merchant.',
    //                 ], 422);
    //             }

    //             $response = \Illuminate\Support\Facades\Http::withHeaders([
    //                 'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
    //                 'accept' => 'application/json',
    //                 'content-type' => 'application/json',
    //             ])->post('https://api.tap.company/v2/charges', [
    //                 'amount' => $service->price,
    //                 'currency' => 'SAR',
    //                 'customer' => [
    //                     'first_name' => $request->customer_name,
    //                     'email' => $request->email,
    //                     'phone' => [
    //                         'country_code' => '966',
    //                         'number' => $request->phone,
    //                     ],
    //                 ],
    //                 'source' => ['id' => 'src_all'],
    //                 'redirect' => ['url' => url('/api/payment/callback')],
    //                 'metadata' => [
    //                     'booking_id' => $booking->id,
    //                 ],
    //             ]);

    //             $resData = $response->json();

    //             if ($response->successful() && isset($resData['transaction']['url'])) {

    //                 $merchantPayment->update([
    //                     'transaction_id' => $resData['id'] ?? null,
    //                 ]);

    //                 return response()->json([
    //                     'success' => true,
    //                     'payment_url' => $resData['transaction']['url'],
    //                     'booking_id' => $booking->id,
    //                 ], 200);
    //             }

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Tap API Error: ' . ($resData['errors'][0]['description'] ?? 'Transaction failed'),
    //             ], 400);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Booking confirmed!',
    //             'booking' => [
    //                 'booking_id' => 'BOK' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
    //                 'service' => $booking->service->service_name,
    //                 'date_time' => $booking->date_time->format('Y-m-d h:i A'),
    //                 'staff_name' => $booking->staff->name,
    //                 'duration' => $booking->service->duration . ' min',
    //                 'amount' => $booking->service->price . ' SAR',
    //                 'payment_method' => $booking->merchantPayment->payment_method,
    //                 'transaction_id' => $booking->merchantPayment->transaction_id,
    //             ]
    //         ], 201);
    //     });
    // }

    public function store(Request $request)
    {
        $merchant = auth()->user();

        // 1. Validate incoming data
        $request->validate([
            'service_id'     => 'required|exists:services,id',
            'staff_id'       => 'nullable|integer',
            'branch_id'      => 'nullable|integer',
            'date'           => 'required|date',
            'time'           => 'required',
            'customer_name'  => 'required|string',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string',
            'special_note'   => 'nullable|string',
            'payment_method' => 'required|in:tap,cash',
        ]);

        // Fallback chain for branch logic
        $activeBranchId = $request->header('X-Branch-Id') ?? $request->branch_id;

        // 2. Pre-database checks (Subscription Plan limits)
        $userSubscription = Subscription::where('user_id', $merchant->id)->first();
        if ($userSubscription && $userSubscription->plan_id == 1) {
            $bookingCount = Booking::where('user_id', $merchant->id)->count();
            if ($bookingCount >= 25) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current plan only allows a maximum of 25 bookings.'
                ], 403);
            }
        }

        // 3. Database Validation & Reservation Phase
        // Wrap ONLY the DB mutations and row-locks here. Keep HTTP requests OUTSIDE.
        try {
            $bookingData = DB::transaction(function () use ($request, $merchant, $activeBranchId) {

                $service = Service::where('id', $request->service_id)
                    ->where('user_id', $merchant->id)
                    ->first();

                if (!$service) {
                    throw new \Exception('Invalid service selection', 422);
                }

                $storeSetting = DB::table('merchant_store_settings')
                    ->where('user_id', $merchant->id)
                    ->first();

                if (!$storeSetting || !$storeSetting->time_zone) {
                    throw new \Exception('Store timezone not set', 422);
                }

                $merchantTimeZone = $storeSetting->time_zone;
                $duration = (int) $service->duration;

                $date = Carbon::parse($request->date, $merchantTimeZone)->startOfDay();
                $today = Carbon::now($merchantTimeZone)->startOfDay();

                if ($date->lt($today)) {
                    throw new \Exception('Selected date is in the past', 422);
                }

                $day = strtolower($date->format('l'));
                $businessHour = DB::table('business_hours')
                    ->where('merchant_store_setting_id', $storeSetting->id)
                    ->where('day', $day)
                    ->where('is_closed', 0)
                    ->first();

                if (!$businessHour || !$businessHour->open_time || !$businessHour->close_time) {
                    throw new \Exception('Invalid slot selected', 422);
                }

                // Slot availability parsing
                $validSlots = [];
                $start = Carbon::createFromTimeString($businessHour->open_time, $merchantTimeZone);
                $end = Carbon::createFromTimeString($businessHour->close_time, $merchantTimeZone);

                while ($start->copy()->addMinutes($duration)->lte($end)) {
                    $validSlots[] = $start->format('H:i');
                    $start->addMinutes($duration);
                }

                $selectedTime = Carbon::parse($request->time)->format('H:i');
                if (!in_array($selectedTime, $validSlots)) {
                    throw new \Exception('Invalid slot selected', 422);
                }

                $slotStart = Carbon::parse($request->date . ' ' . $request->time, $merchantTimeZone);
                $slotEnd = $slotStart->copy()->addMinutes($duration);

                if ($slotStart->lt(Carbon::now($merchantTimeZone))) {
                    throw new \Exception('Selected time is in the past', 422);
                }

                // Staff & Slot Booking validation with Pessimistic Locking
                $staffQuery = Staff::where('user_id', $merchant->id)
                    ->whereJsonContains('service_id', (string)$service->id)
                    ->where('status', 1);

                $staffCount = $staffQuery->count();
                if ($staffCount == 0) {
                    throw new \Exception('No staff available for this service', 404);
                }

                $existingBookings = Booking::whereIn('staff_id', function ($q) use ($merchant, $service) {
                        $q->select('id')
                            ->from('staffs')
                            ->where('user_id', $merchant->id)
                            ->whereJsonContains('service_id', (string)$service->id)
                            ->where('status', 1);
                    })
                    ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                    ->where(function ($q) use ($slotStart, $slotEnd) {
                        $q->where('date_time', '<', $slotEnd)
                        ->whereRaw("DATE_ADD(date_time, INTERVAL (SELECT duration FROM services WHERE services.id = bookings.service_id) MINUTE) > ?", [$slotStart]);
                    })
                    ->lockForUpdate()
                    ->count();

                if ($existingBookings >= $staffCount) {
                    throw new \Exception('This slot is fully booked.', 409);
                }

                $staff = $staffQuery->inRandomOrder()->first();
                if (!$staff) {
                    throw new \Exception('No staff available', 404);
                }

                // Create Booking
                $booking = Booking::create([
                    'user_id'       => $merchant->id,
                    'staff_id'      => $staff->id,
                    'service_id'    => $service->id,
                    'branch_id'     => $activeBranchId,
                    'customer_name' => $request->customer_name,
                    'email'         => $request->email,
                    'phone'         => $request->phone,
                    'date_time'     => $slotStart,
                    'status'        => $request->payment_method === 'cash' ? 'confirm' : 'pending',
                    'special_note'  => $request->special_note,
                    'booking_by'    => 'merchant',
                ]);

                // Create Payment Log
                $merchantPayment = MerchantPayment::create([
                    'booking_id'     => $booking->id,
                    'user_id'        => $merchant->id,
                    'branch_id'      => $activeBranchId,
                    'payment_method' => $request->payment_method,
                    'amount'         => $service->price,
                    'transaction_id' => $request->payment_method === 'cash' ? 'cash-' . uniqid() : null,
                    'payment_status' => $request->payment_method === 'cash' ? 'paid' : 'pending',
                    'paid_at'        => $request->payment_method === 'cash' ? Carbon::now($merchantTimeZone) : null,
                ]);

                // Pass everything needed outside the transaction closure
                return compact('booking', 'merchantPayment', 'service', 'staff', 'merchantTimeZone');
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], is_numeric($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 422);
        }

        // Extract transaction items back into local scope
        extract($bookingData);

        // 4. Post-Transaction Phase: Send Emails (Safe from rollbacks)
        try {
            if ($booking->email) {
                Mail::to($booking->email)->send(new BookingCreateMail($booking));
            }
        } catch (\Exception $e) {
            Log::error('Booking email failed: ' . $e->getMessage(), ['booking_id' => $booking->id]);
        }

        // 5. Post-Transaction Phase: External Gateway API Communication
        if ($request->payment_method === 'tap') {

            $tapSetting = DB::table('tap_payments')
                ->where('user_id', $merchant->id)
                ->latest('updated_at')
                ->first();

            if (!$tapSetting || !$tapSetting->tap_secret_key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tap Payment details not found for this merchant.',
                ], 422);
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
                'accept'        => 'application/json',
                'content-type'  => 'application/json',
            ])->post('https://api.tap.company/v2/charges', [
                'amount'   => $service->price,
                'currency' => 'SAR',
                'customer' => [
                    'first_name' => $request->customer_name,
                    'email'      => $request->email,
                    'phone'      => [
                        'country_code' => '966',
                        'number'       => $request->phone,
                    ],
                ],
                'source'   => ['id' => 'src_all'],
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
                    'success'     => true,
                    'payment_url' => $resData['transaction']['url'],
                    'booking_id'  => $booking->id,
                ], 200);
            }

            // If Tap fails, update booking status to failed so slot releases up
            $booking->update(['status' => 'cancelled']);

            return response()->json([
                'success' => false,
                'message' => 'Tap API Error: ' . ($resData['errors'][0]['description'] ?? 'Transaction failed'),
            ], 400);
        }

        // 6. Return response for standard 'Cash' checkout
        $formattedDateTime = Carbon::parse($booking->date_time, $merchantTimeZone)->format('Y-m-d h:i A');

        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed!',
            'booking' => [
                'booking_id'     => 'BOK' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                'service'        => $service->service_name,
                'date_time'      => $formattedDateTime,
                'staff_name'     => $staff->name,
                'duration'       => $service->duration . ' min',
                'amount'         => $service->price . ' SAR',
                'payment_method' => $merchantPayment->payment_method,
                'transaction_id' => $merchantPayment->transaction_id,
            ]
        ], 201);
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
                    'message' => 'Booking confirmed!',
                    'booking' => [
                        'booking_id' => 'BOK' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                        'service' => $booking->service->service_name,
                        'date_time' => Carbon::parse($booking->date_time)->format('Y-m-d h:i A'),
                        'staff_name' => $booking->staff->name,
                        'duration' => $booking->service->duration . ' min',
                        'amount' => $booking->service->price . ' SAR',
                        'payment_method' => $booking->merchantPayment->payment_method,
                        'transaction_id' => $booking->merchantPayment->transaction_id,
                    ]
                ], 201);
            }
        } catch (\Exception $e) {
            Log::error('Payment callback error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the payment callback.',
            ], 500);
        }
    }


    public function bookingInvoice($bookingId)
    {
        $merchantId = auth()->id();

        $booking = Booking::with([
            'service:id,service_name,duration,price',
            'staff:id,name',
            'merchant:id,name,email,phone',
            'merchantStore:id,user_id,store_name,business_address',
            'merchantPayment'
        ])
            ->where('id', $bookingId)
            ->where('user_id', $merchantId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $payment = $booking->merchantPayment;

        $invoice = [

            'invoice_info' => [
                'invoice_no' => 'INV-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'booking_id' => 'BOK' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                'invoice_date' => $booking->created_at->format('M d, Y'),
            ],

            'merchant_info' => [
                'business_logo' => $booking->merchantStore->business_logo ?? null,
                'business_name' => $booking->merchantStore->store_name ?? null,
                'merchant_name' => $booking->merchant->name ?? null,
                'email' => $booking->merchant->email ?? null,
                'phone' => $booking->merchant->phone ?? null,
                'address' => $booking->merchantStore->business_address ?? null,
            ],

            'customer_info' => [
                'name' => $booking->customer_name,
                'email' => $booking->email,
                'phone' => $booking->phone,
            ],

            'booking_details' => [
                'service' => $booking->service->service_name,
                'staff' => $booking->staff->name ?? 'Any Staff',
                'duration' => $booking->service->duration . ' min',
                'booking_time' => Carbon::parse($booking->date_time)->format('M d, Y h:i A'),
            ],

            'payment_details' => [
                'payment_method' => ucfirst($payment->payment_method),
                'transaction_id' => $payment->transaction_id,
                'status' => ucfirst($payment->payment_status),
                'paid_at' => $payment->paid_at
                    ? Carbon::parse($payment->paid_at)->format('M d, Y h:i A')
                    : null,
            ],

            'summary' => [
                'service_price' => $booking->service->price,
                'tax' => 0,
                'discount' => 0,
                'total_amount' => $booking->service->price,
                'currency' => 'SAR'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
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

        $staffIds = Staff::where('user_id', $merchantId)
            ->where('status', 1)
            ->pluck('id');

        if ($staffIds->isEmpty()) {
            return response()->json([
                'available_times' => [],
                'message' => 'No staff available'
            ], 404);
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
                ->whereDate('date_time', $date)
                ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                ->get();
        } else {
            $bookings = Booking::whereIn('staff_id', $staffIds)
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

                if ($slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart)) {
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
            ->whereJsonContains('service_id', (string)$service->id)
            ->where('status', 1)
            ->pluck('id');

        if ($staffIds->isEmpty()) {
            return response()->json([
                'available_staff' => [],
                'message' => 'No staff available for this service',
            ], 404);
        }

        $bookings = Booking::whereIn('staff_id', $staffIds)
            ->whereDate('date_time', $request->date)
            ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
            ->get();

        $bookedStaff = [];
        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->date_time, $merchantTimeZone);
            $bookingEnd = $bookingStart->copy()->addMinutes($booking->service->duration);

            if ($slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart)) {
                $bookedStaff[] = $booking->staff_id;
            }
        }

        $availableStaff = Staff::whereIn('id', array_diff($staffIds->toArray(), $bookedStaff))
            ->get();

        return response()->json([
            'available_staff' => $availableStaff,
        ]);
    }




    public function bookingByUser(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
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

    // start
    $subscription = DB::table('subscriptions')
        ->where('user_id', $merchantId)
        ->first();

    if ($subscription && $subscription->plan_id == 1) {
        $totalBookingsCount = Booking::where('user_id', $merchantId)
            ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
            ->count();

        if ($totalBookingsCount >= 25) {
            return response()->json([
                'success' => false,
                'message' => 'The merchant has reached the total maximum booking limit of 25 for this plan.'
            ], 403);
        }
    }
    // end


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
                    ->whereJsonContains('service_id', (string)$service->id)
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
                    ->whereJsonContains('service_id', (string)$service->id)
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
                'branch_id'     => $request->branch_id,
                'service_id' => $service->id,
                'customer_name' => $request->customer_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_time' => $slotStart,
                'status' => 'pending',
                'special_note' => $request->special_note,
                // 'booking_by' => auth()->id(),
                'booking_by'    => auth('api')->check() ? auth('api')->id() : 'guest',
            ]);

            $payment = MerchantPayment::create([
                'booking_id' => $booking->id,
                'user_id' => $merchantId,
                'branch_id'      => $request->branch_id,
                'payment_method' => $request->payment_method,
                'amount' => $service->price,
                'transaction_id' => 'tx' . uniqid(),
                'payment_status' => 'due',
            ]);

            if ($request->payment_method == 'cash') {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking confirmed!',
                    'booking' => [
                        'booking_id' => 'BOK' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                        'service' => $booking->service->service_name,
                        'branch_id'  => $booking->branch_id,
                        'date_time' => $booking->date_time->format('Y-m-d h:i A'),
                        'staff_name' => $booking->staff->name,
                        'duration' => $booking->service->duration . ' min',
                        'amount' => $booking->service->price . ' SAR',
                        'payment_method' => $booking->merchantPayment->payment_method,
                        'transaction_id' => $booking->merchantPayment->transaction_id,
                    ]
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

                    // 'redirect' => [
                    //     'url' => url('/api/tap-success?booking_id=' . $booking->id),
                    // ],

                    'redirect' => [
                        'url' => url('/api/tap-callback?booking_id=' . $booking->id),
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
                    'branch_id'   => $booking->branch_id
                ], 200);
            }
        });
    }



    public function tapCallbackbooking(Request $request)
    {
        $bookingId = $request->booking_id;

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

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $merchantId)
            ->first();

        $merchantTimeZone = $storeSetting->time_zone ?? 'UTC';

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
            ], 500);
        }

        $tapData = $tapResponse->json();

        $booking = null;

        DB::transaction(function () use ($tapData, $payment, $bookingId, &$booking, $merchantTimeZone) {

            if ($tapData['status'] == 'CAPTURED') {

                $payment->update([
                    'payment_status' => 'paid',
                    'paid_at' => Carbon::now($merchantTimeZone),
                ]);

                Booking::where('id', $bookingId)->update([
                    'status' => 'confirm',
                ]);

                $booking = Booking::with(['service', 'staff', 'merchantPayment', 'branch'])
                    ->find($bookingId);
            } else {

                $payment->update([
                    'payment_status' => 'failed',
                ]);

                Booking::where('id', $bookingId)->update([
                    'status' => 'cancel',
                ]);
            }
        });

        if ($tapData['status'] == 'CAPTURED' && $booking) {

            Mail::to($booking->email)
                ->send(new BookingConfirmationMail($booking));

            // Merchant Mail
            $merchant = User::find($booking->user_id);

            if ($merchant && $merchant->email) {

                Mail::to($merchant->email)
                    ->send(new MerchantBookingNotificationMail($booking));
            }
        }

        $frontendBaseUrl = env('FRONTEND_URL', 'http://bokli.io');

        if ($tapData['status'] == 'CAPTURED') {
            $frontendUrl = $frontendBaseUrl . "/booking-success?booking_id=" . $bookingId;
        } else {
            $frontendUrl = $frontendBaseUrl . "/booking-failed?booking_id=" . $bookingId;
        }

        return redirect()->away($frontendUrl);
    }

    public function bookingDetails($id)
    {
        $booking = Booking::with(['service', 'staff', 'merchantPayment'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed!',
            'data' => [
                'booking_id' => 'BOK' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                'service' => $booking->service->service_name,
                'staff' => $booking->staff->name,
                'date_time' => Carbon::parse($booking->date_time)->format('Y-m-d h:i A'),
                'duration' => $booking->service->duration . ' min',
                'amount' => $booking->merchantPayment->amount . ' SAR',
                'payment_method' => $booking->merchantPayment->payment_method,
                'transaction_id' => $booking->merchantPayment->transaction_id,
            ]
        ]);
    }

    public function invoice($bookingId)
    {
        $userId = auth()->id();

        $booking = Booking::with([
            'service:id,service_name,duration,price',
            'staff:id,name',
            'merchant:id,name,email,phone',
            'merchantStore:id,user_id,store_name,business_address,business_logo',
            'merchantPayment'
        ])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $payment = $booking->merchantPayment;

        $invoice = [

            'invoice_info' => [
                'invoice_no' => 'INV-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'booking_id' => 'BOK' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                'invoice_date' => $booking->created_at->format('M d, Y'),
            ],

            'merchant_info' => [
                'business_logo' => $booking->merchantStore->business_logo ?? null,
                'business_name' => $booking->merchantStore->store_name ?? '',
                'merchant_name' => $booking->merchant->name ?? '',
                'email' => $booking->merchant->email ?? '',
                'phone' => $booking->merchant->phone ?? '',
                'address' => $booking->merchantStore->business_address ?? '',
            ],

            'customer_info' => [
                'name' => $booking->customer_name,
                'email' => $booking->email,
                'phone' => $booking->phone,
            ],

            'booking_details' => [
                'service' => $booking->service->service_name,
                'staff' => $booking->staff->name ?? 'Any Staff',
                'duration' => $booking->service->duration . ' min',
                'booking_time' => Carbon::parse($booking->date_time)->format('M d, Y h:i A'),
            ],

            'payment_details' => [
                'payment_method' => ucfirst($payment->payment_method ?? ''),
                'transaction_id' => $payment->transaction_id ?? '',
                'status' => ucfirst($payment->payment_status ?? ''),
                'paid_at' => $payment->paid_at
                    ? Carbon::parse($payment->paid_at)->format('M d, Y h:i A')
                    : '',
            ],

            'summary' => [
                'service_price' => $booking->service->price,
                'tax' => 0,
                'discount' => 0,
                'total_amount' => $booking->service->price,
                'currency' => 'SAR'
            ]
        ];

        $pdf = Pdf::loadView('invoice', compact('invoice'));

        return $pdf->download('invoice-' . $booking->id . '.pdf');
    }


    public function booklischedule(Request $request, $website_domain)
    {
        $user = User::where('website_domain', $website_domain)->first();

        if (!$user) {
            return response()->json(['available_times' => [], 'message' => 'Store not found'], 404);
        }

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
            'staff_id'   => 'nullable|integer',
        ]);

        $service = Service::find($request->service_id);


        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $user->id)
            ->first();

        if (!$storeSetting || !$storeSetting->time_zone) {
            return response()->json(['available_times' => [], 'message' => 'Store timezone not set']);
        }

        $merchantTimeZone = $storeSetting->time_zone;
        $date = Carbon::parse($request->date, $merchantTimeZone);
        $day = strtolower($date->format('l'));

        $businessHour = BusinessHour::where('merchant_store_setting_id', $storeSetting->id)
            ->where('day', $day)
            ->where('is_closed', 0)
            ->first();

        if (!$businessHour || !$businessHour->open_time || !$businessHour->close_time) {
            return response()->json(['available_times' => [], 'message' => 'Business closed today']);
        }

        $staffQuery = Staff::where('user_id', $user->id)->where('status', 1);

        if ($request->staff_id) {
            $staffQuery->where('id', $request->staff_id);
        }

        $staffIds = $staffQuery->pluck('id');

        if ($staffIds->isEmpty()) {
            return response()->json(['available_times' => [], 'message' => 'No staff available'], 404);
        }

        $duration = (int) $service->duration;
        $slots = [];
        $start = Carbon::createFromTimeString($businessHour->open_time, $merchantTimeZone);
        $end = Carbon::createFromTimeString($businessHour->close_time, $merchantTimeZone);

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($duration);
        }

        $bookings = Booking::whereIn('staff_id', $staffIds)
            ->whereDate('date_time', $date)
            ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
            ->with('service')
            ->get();

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
                $bookingEnd = $bookingStart->copy()->addMinutes($booking->service->duration ?? $duration);

                if ($slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart)) {
                    $overlapStaff[$booking->staff_id] = true;
                }
            }

            if (count($overlapStaff) < count($staffIds)) {
                $availableSlots[] = $slotStart->format('h:i A');
            }
        }

        return response()->json([
            'success' => true,
            'store_name' => $user->name,
            'available_times' => $availableSlots
        ]);
    }
}
