<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingRescheduledMail;
use App\Mail\BookingCancelledMail;
use App\Models\Booking;
use App\Models\BusinessHour;
use App\Models\MerchantPayment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class UserDashboardController extends Controller
{
    public function Upcoming(Request $request)
    {
        $userId = $request->user()->id;

        $booking = Booking::with([
            'service:id,service_name,duration,price,user_id',
            'service.merchant:id,name,phone,address',
        ])
            ->where('booking_by', $userId)
            ->whereIn('status', ['confirm', 'rescheduled'])
            ->whereHas('payment', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->orderBy('date_time', 'asc')
            ->get()
            ->first(function ($booking) {

                $storeSetting = DB::table('merchant_store_settings')
                    ->where('user_id', $booking->service->user_id)
                    ->first();

                if (! $storeSetting || ! $storeSetting->time_zone) {
                    return false;
                }

                $merchantNow = Carbon::now($storeSetting->time_zone);
                $bookingTime = Carbon::parse($booking->date_time, $storeSetting->time_zone);

                return $bookingTime->gte($merchantNow);
            });

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'No upcoming appointment found.',
            ]);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->service->user_id)
            ->first();

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);

        $result = [
            'booking_id' => $booking->id,
            'service_id' => $booking->service->id ?? null,
            'service_name' => $booking->service->service_name ?? null,
            'staff' => $booking->staff->name ?? null,
            'status' => ucfirst($booking->status),
            'address' => $booking->service->merchant->address ?? null,

            'booking_date' => $bookingDateTime->format('M d, Y'),
            'booking_time' => $bookingDateTime->format('h:i A'),

            'service_price' => $booking->service->price . ' SAR' ?? null,
            'merchant_phone' => $booking->service->merchant->phone ?? null,
        ];

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function Activity()
    {
        $userId = auth()->id();

        $activities = collect();

        $bookings = Booking::where('booking_by', $userId)
            ->select('id', 'status', 'created_at', 'updated_at')
            ->latest('created_at')
            ->limit(1)
            ->get();

        foreach ($bookings as $booking) {

            $activities->push([
                'title' => 'Appointment booked',
                'time' => $booking->created_at,
            ]);

            if ($booking->status === 'rescheduled') {
                $activities->push([
                    'title' => 'Appointment rescheduled',
                    'time' => $booking->updated_at,
                ]);
            }

            if ($booking->status === 'cancel') {
                $activities->push([
                    'title' => 'Appointment cancelled',
                    'time' => $booking->updated_at,
                ]);
            }
        }

        $bookingIds = $bookings->pluck('id')->toArray();

        $payments = MerchantPayment::whereIn('booking_id', $bookingIds)
            ->whereIn('payment_status', ['paid'])
            ->select('amount', 'paid_at')
            ->get();
        foreach ($payments as $payment) {
            $activities->push([
                'title' => 'Payment completed - ' . $payment->amount . ' SAR',
                'time' => $payment->paid_at,
            ]);
        }

        $activities = $activities
            ->sortByDesc('time')
            ->take(10)
            ->values()
            ->map(function ($item) {
                return [
                    'title' => $item['title'],
                    'time' => Carbon::parse($item['time'])->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function History(Request $request)
    {
        $userId = $request->user()->id;

        $query = Booking::with([
            'service:id,service_name,duration,price,user_id',
            'service.merchant:id,name,phone,business_category',
            'bookedUser:id,name,image',
        ])
            ->where('booking_by', $userId);

        if ($request->filled('date_filter')) {
            if ($request->date_filter === '7_days') {
                $query->where('date_time', '>=', Carbon::now()->subDays(7));
            }

            if ($request->date_filter === '30_days') {
                $query->where('date_time', '>=', Carbon::now()->subDays(30));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_name')) {
            $query->whereHas('service', function ($q) use ($request) {
                $q->where('service_name', 'LIKE', '%' . $request->service_name . '%');
            });
        }

        $bookings = $query
            ->orderBy('date_time', 'desc')
            ->paginate(10);

        $statusMap = [
            'pending' => 'Pending',
            'confirm' => 'Confirmed',
            'complete' => 'Completed',
            'cancel' => 'Canceled',
            'rescheduled' => 'Rescheduled',
        ];

        $result = $bookings->getCollection()->map(function ($booking) use ($statusMap) {
            return [
                'booking_id' => $booking->id,
                'customer_image' => $booking->bookedUser->image ?? null,
                'customer' => $booking->bookedUser->name ?? null,
                'service_name' => $booking->service->service_name ?? null,
                'amount' => $booking->service->price ?? null,
                'booking_date' => Carbon::parse($booking->date_time)->format('M d, Y h:i A'),
                'duration' => $booking->service->duration ?? null,
                'status' => $statusMap[$booking->status] ?? ucfirst($booking->status),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {

        $userId = auth()->user()->id;

        $booking = Booking::with([
            'bookedUser:id,name,email,phone,image',
            'service:id,service_name,duration,price',
            'staff:id,name',
        ])
            ->where('id', $id)
            ->where('booking_by', $userId)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'No booking found for this user.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'name' => $booking->bookedUser->name ?? null,
                    'email' => $booking->bookedUser->email ?? null,
                    'phone' => $booking->bookedUser->phone ?? null,
                ],

                'booking' => [
                    'booking_id' => $booking->id,
                    'service' => $booking->service->service_name ?? null,
                    'date_time' => Carbon::parse($booking->date_time)->format('M d, Y h:i A'),
                    'duration' => $booking->service->duration . ' min' ?? null,
                    'staff' => $booking->staff->name ?? 'Not Assigned',
                    'price' => $booking->service->price ?? null,
                    'status' => ucfirst($booking->status),
                ],
            ],
        ]);
    }

    public function paymentHistory(Request $request)
    {
        $userId = auth()->user()->id;

        $query = Booking::with([
            'merchantPayment',
            'service',
            'merchantStore:id,user_id,store_name,business_logo',
            'merchant:id,name,email',
        ])
            ->where('booking_by', $userId);

        if ($request->filled('status')) {
            $query->whereHas('merchantPayment', function ($q) use ($request) {
                $q->where('payment_status', $request->status);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $payments->getCollection()->map(function ($booking) {
            $payment = $booking->merchantPayment;

            return [
                'booking_id' => $booking->id,
                'store_logo' => $booking->merchantStore->business_logo ?? null,
                'store_name' => $booking->merchantStore->store_name ?? null,
                'service' => $booking->service->service_name ?? null,
                'date_time' => Carbon::parse($booking->date_time)->format('M d, Y h:i A'),
                'amount' => $payment ? $payment->amount . ' SAR' : '0 SAR',
                'status' => ucfirst($payment->payment_status ?? $booking->status),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $payments->total(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
            ],
        ]);
    }

    public function showPayment($bookingId)
    {

        $userId = auth()->user()->id;

        $booking = Booking::with([
            'merchantPayment',
            'merchantStore:id,user_id,store_name,business_logo',
            'merchant:id,name,email,phone',
        ])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'No payment information found for this user. Please check your booking ID.',
            ], 404);
        }

        $payment = $booking->merchantPayment;

        $paymentMethod = $payment->payment_method;

        $data = [
            'payment_status' => ucfirst($payment->payment_status ?? $booking->status),
            'transaction_info' => [
                'transaction_id' => $payment->transaction_id ?? '#TX' . str_pad($booking->id, 3, '0', STR_PAD_LEFT),
                'amount' => $payment->amount ?? 0,
                'date_time' => Carbon::parse($payment->paid_at ?? $booking->created_at)->format('M d, Y h:i A'),
                'payment_method' => $paymentMethod,
            ],
            'customer_info' => [
                'merchant_name' => $booking->merchant->name ?? null,
                'business_name' => $booking->merchantStore->store_name ?? null,
                'email' => $booking->merchant->email ?? null,
                'phone' => $booking->merchant->phone ?? null,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function viewOrderDetails($bookingId)
    {
        $userId = auth()->user()->id;

        $booking = Booking::with([
            'service',
            'staff',
            'merchant',
            'merchantStore',
            'merchantPayment',
        ])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'No booking found for this user. Please check your booking ID and try again.',
            ], 404);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->service->user_id)
            ->first();

        if (! $storeSetting || ! $storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone not set.',
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow = Carbon::now($merchantTimeZone);

        if ($bookingDateTime->lt($merchantNow)) {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already expired.',
            ], 400);
        }

        $payment = $booking->merchantPayment;
        $paymentStatus = ucfirst($payment->payment_status ?? $booking->status);

        $data = [
            'merchant_info' => [
                'merchant_name' => $booking->merchantStore->store_name ?? $booking->merchant->name ?? null,
                'location' => $booking->merchantStore->business_address ?? null,
                'phone' => $booking->merchant->phone ?? null,
            ],
            'booking_info' => [
                'booking_id' => $booking->id,
                'service_id' => $booking->service->id ?? null,
                'service_name' => $booking->service->service_name ?? null,

                'date_time' => $bookingDateTime->format('M d, Y h:i A'),

                'duration' => $booking->service->duration ?? null,
                'staff_name' => $booking->staff->name ?? 'Not Assigned',
                'price' => $booking->service->price ?? 0,
                'payment_status' => $paymentStatus,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function cancelPreview(Request $request, $bookingId)
    {
        $userId = $request->user()->id;

        $booking = Booking::with(['service'])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'No booking found for this user. Please check your booking ID and try again.',
            ], 404);
        }

        if ($booking->status === 'cancel') {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already been cancelled.',
            ], 400);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->user_id)
            ->first();

        if (! $storeSetting || ! $storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone not set.',
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow = Carbon::now($merchantTimeZone);

        if ($bookingDateTime->lt($merchantNow)) {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already expired.',
            ], 400);
        }

        $data = [
            'service_id' => $booking->service->id ?? 'N/A',
            'service_name' => $booking->service->service_name ?? 'N/A',
            'booking_date' => $bookingDateTime->format('M d, Y'),
            'booking_time' => $bookingDateTime->format('h:i A'),
            'note' => 'Cancellation policies may apply. Please check with the merchant for refund details.',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Booking cancellation preview. Please review the details before proceeding.',
            'data' => $data,
        ]);
    }

    public function cancelBooking(Request $request, $bookingId)
    {
        $userId = $request->user()->id;

        $booking = Booking::with('merchantPayment')
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'No booking found for this user. Please check your booking ID and try again.',
            ], 404);
        }

        if ($booking->status === 'cancel') {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already been cancelled.',
            ], 400);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->user_id)
            ->first();

        if (! $storeSetting || ! $storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone is not set.',
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow = Carbon::now($merchantTimeZone);

        if ($bookingDateTime->lt($merchantNow)) {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already expired.',
            ], 400);
        }

        $minutesDifference = $merchantNow->diffInMinutes($bookingDateTime, false);

        if ($minutesDifference < 120) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot cancel this booking within 2 hours of the scheduled time.',
            ], 403);
        }

        $message = 'Your booking has been cancelled successfully.';

        $canCancel = true;

        $payment = $booking->merchantPayment;

        if ($payment) {

            if ($payment->payment_method === 'tap' && $payment->payment_status === 'paid') {

                $tapSetting = DB::table('tap_payments')
                    ->where('user_id', $booking->user_id)
                    ->latest()
                    ->first();

                if ($tapSetting) {

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $tapSetting->tap_secret_key,
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ])->post('https://api.tap.company/v2/refunds', [
                        'charge_id' => $payment->transaction_id,
                        'amount' => $payment->amount,
                        'currency' => 'SAR',
                        'reason' => 'Booking cancelled',
                    ]);

                    $resData = $response->json();

                    if ($response->successful() && isset($resData['id'])) {

                        $payment->update([
                            'payment_status' => 'refunded',
                            'refund_id' => $resData['id'],
                            'refund_date' => now(),
                        ]);

                        $message = 'Your booking has been cancelled successfully. The payment has been refunded to your account.';
                    } else {

                        $payment->update([
                            'payment_status' => 'refund_failed',
                        ]);

                        $canCancel = false;
                        $message = 'Refund failed. Booking was not cancelled. Please contact support.';
                    }
                }
            } else {

                $payment->update([
                    'payment_status' => 'failed',
                ]);

                $message = 'Your booking has been cancelled successfully.';
            }
        }

        if (! $canCancel) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 400);
        }

        $booking->update([
            'status' => 'cancel',
        ]);

        try {
            Mail::to($booking->email)
                ->send(new BookingCancelledMail($booking, $message));
        } catch (\Exception $e) {
            Log::error('Cancel email failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function reschedulePreview(Request $request, $bookingId)
    {
        $booking = Booking::with(['service', 'staff'])
            ->where('id', $bookingId)
            ->where('booking_by', auth()->id())
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        if ($booking->status === 'cancel') {
            return response()->json([
                'success' => false,
                'message' => 'Cancelled booking cannot be rescheduled.',
            ], 400);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->service->user_id)
            ->first();

        if (! $storeSetting || ! $storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone not set.',
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow = Carbon::now($merchantTimeZone);

        if ($bookingDateTime->lt($merchantNow)) {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already expired. Expired booking cannot be rescheduled.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'booking_id' => $booking->id,
                'service_id' => $booking->service->id ?? null,
                'service' => $booking->service->service_name ?? null,
                'current_date' => $bookingDateTime->format('M d, Y'),
                'current_time' => $bookingDateTime->format('h:i A'),
                'staff' => $booking->staff->name ?? 'Any staff',
            ],
        ]);
    }

    public function rescheduleBooking(Request $request, $bookingId)
    {
        return DB::transaction(function () use ($request, $bookingId) {
            $request->validate([
                'date' => 'required|date',
                'time' => 'required',
                'staff_id' => 'nullable|integer',
            ]);

            $booking = Booking::where('id', $bookingId)
                ->where('booking_by', auth()->id())
                ->lockForUpdate()
                ->first();

            if (! $booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.',
                ], 404);
            }

            if ($booking->status === 'cancel') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cancelled booking cannot be rescheduled.',
                ], 400);
            }

            $storeSetting = DB::table('merchant_store_settings')
                ->where('user_id', $booking->user_id)
                ->first();

            if (! $storeSetting || ! $storeSetting->time_zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store timezone is not set.',
                ], 400);
            }

            $merchantTimeZone = $storeSetting->time_zone;

            $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
            $merchantNow = Carbon::now($merchantTimeZone);

            if ($bookingDateTime->lt($merchantNow)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking has already expired. Expired booking cannot be rescheduled.',
                ], 400);
            }

            $date = Carbon::parse($request->date, $merchantTimeZone)->startOfDay();
            $today = Carbon::now($merchantTimeZone)->startOfDay();

            if ($date->lt($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected date is in the past',
                ], 422);
            }

            $newDateTime = Carbon::parse($request->date . ' ' . $request->time, $merchantTimeZone);

            if ($newDateTime->lte($merchantNow)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected time is in the past.',
                ], 400);
            }

            $day = strtolower($newDateTime->format('l'));

            $businessHour = BusinessHour::where('merchant_store_setting_id', $storeSetting->id)
                ->where('day', $day)
                ->where('is_closed', 0)
                ->first();

            if (! $businessHour || ! $businessHour->open_time || ! $businessHour->close_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid slot selected.',
                ], 422);
            }

            $service = Service::find($booking->service_id);
            $duration = (int) $service->duration;

            $start = Carbon::createFromTimeString($businessHour->open_time, $merchantTimeZone);
            $end = Carbon::createFromTimeString($businessHour->close_time, $merchantTimeZone);

            $validSlots = [];

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $validSlots[] = $start->format('H:i');
                $start->addMinutes($duration);
            }

            if (! in_array($newDateTime->format('H:i'), $validSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid slot selected.',
                ], 422);
            }

            if ($merchantNow->diffInMinutes($bookingDateTime, false) <= 120) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot reschedule within 2 hours of the booked time.',
                ], 403);
            }

            $staff = null;

            if ($request->filled('staff_id')) {

                $staff = Staff::where('id', $request->staff_id)
                    ->where('user_id', $booking->user_id)
                    ->where('status', 1)
                    ->first();

                if (! $staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid staff selected.',
                    ], 400);
                }
            } else {

                $staff = Staff::where('user_id', $booking->user_id)
                    ->where('status', 1)
                    ->whereNotIn('id', function ($query) use ($newDateTime, $booking) {
                        $query->select('staff_id')
                            ->from('bookings')
                            ->where('date_time', $newDateTime)
                            ->where('status', '!=', 'cancel')
                            ->where('id', '!=', $booking->id);
                    })
                    ->inRandomOrder()
                    ->first();

                if (! $staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No available staff for selected time.',
                    ], 409);
                }
            }

            $slotStart = $newDateTime;
            $slotEnd = $newDateTime->copy()->addMinutes($duration);

            $conflict = Booking::where('staff_id', $staff->id)
                ->whereIn('status', ['pending', 'confirm', 'rescheduled'])
                ->where('id', '!=', $booking->id)
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
                    'message' => 'Selected staff is not available at this time.',
                ], 409);
            }

            $booking->update([
                'date_time' => $newDateTime,
                'staff_id' => $staff->id,
                'status' => 'rescheduled',
                'rescheduled_at' => $merchantNow,
            ]);

            try {
                Mail::to($booking->email)
                    ->send(new BookingRescheduledMail($booking));
            } catch (\Exception $e) {
                Log::error('Reschedule email failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking rescheduled successfully.',
            ]);
        });
    }

    public function userInvoice($bookingId)
    {
        $userId = auth()->id();

        $booking = Booking::with([
            'bookedUser:id,name,email,phone',
            'service:id,service_name,price,duration',
            'merchant:id,name,email,phone',
            'merchantStore:id,user_id,store_name,business_logo,business_address',
            'merchantPayment'
        ])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.'
            ], 404);
        }

        $payment = $booking->merchantPayment;

        $invoice = [

            'invoice_info' => [
                'invoice_no' => 'INV-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'booking_id' => $booking->id,
                'date' => Carbon::parse($booking->created_at)->format('M d, Y'),
            ],

            'customer' => [
                'name' => $booking->bookedUser->name ?? null,
                'email' => $booking->bookedUser->email ?? null,
                'phone' => $booking->bookedUser->phone ?? null,
            ],

            'merchant' => [
                'business_logo' => $booking->merchantStore->business_logo ?? null,
                'business_name' => $booking->merchantStore->store_name ?? null,
                'merchant_name' => $booking->merchant->name ?? null,
                'email' => $booking->merchant->email ?? null,
                'phone' => $booking->merchant->phone ?? null,
                'address' => $booking->merchantStore->business_address ?? null,
            ],

            'service' => [
                'service_name' => $booking->service->service_name ?? null,
                'duration' => $booking->service->duration . ' min',
                'price' => $booking->service->price . ' SAR',
                'booking_time' => Carbon::parse($booking->date_time)->format('M d, Y h:i A'),
            ],

            'payment' => [
                'transaction_id' => $payment->transaction_id ?? null,
                'method' => $payment->payment_method ?? null,
                'status' => ucfirst($payment->payment_status ?? 'unpaid'),
                'paid_at' => $payment->paid_at
                    ? Carbon::parse($payment->paid_at)->format('M d, Y h:i A')
                    : null,
            ],

            'summary' => [
                'subtotal' => $booking->service->price ?? 0,
                'tax' => 0,
                'total' => $booking->service->price ?? 0,
                'currency' => 'SAR',
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }
}
