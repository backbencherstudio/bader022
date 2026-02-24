<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\MerchantPayment;
use Illuminate\Support\Facades\DB;


class UserDashboardController extends Controller
{
    public function Upcoming(Request $request)
    {
        $userId = $request->user()->id;

        $booking = Booking::with([
            'service:id,service_name,duration,price,user_id',
            'service.merchant:id,name,phone,business_category'
        ])
            ->where('booking_by', $userId)
            ->whereIn('status', ['confirm', 'pending', 'rescheduled'])
            ->orderBy('date_time', 'asc')
            ->get()
            ->first(function ($booking) {

                $storeSetting = DB::table('merchant_store_settings')
                    ->where('user_id', $booking->service->user_id)
                    ->first();

                if (!$storeSetting || !$storeSetting->time_zone) {
                    return false;
                }

                $merchantNow = Carbon::now($storeSetting->time_zone);
                $bookingTime = Carbon::parse($booking->date_time, $storeSetting->time_zone);

                return $bookingTime->gte($merchantNow);
            });

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'No upcoming appointment found.'
            ]);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->service->user_id)
            ->first();

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);

        $result = [
            'booking_id'        => $booking->id,
            'service_name'      => $booking->service->service_name ?? null,
            'status'            => ucfirst($booking->status),
            'merchant_category' => $booking->service->merchant->business_category ?? null,

            'booking_date'      => $bookingDateTime->format('M d, Y'),
            'booking_time'      => $bookingDateTime->format('h:i A'),

            'service_price'     => $booking->service->price ?? null,
            'merchant_phone'    => $booking->service->merchant->phone ?? null,
        ];

        return response()->json([
            'success' => true,
            'data' => $result
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
                'time'  => $booking->created_at,
            ]);

            if ($booking->status === 'rescheduled') {
                $activities->push([
                    'title' => 'Appointment rescheduled',
                    'time'  => $booking->updated_at,
                ]);
            }

            if ($booking->status === 'cancel') {
                $activities->push([
                    'title' => 'Appointment cancelled',
                    'time'  => $booking->updated_at,
                ]);
            }
        }


        $payments = MerchantPayment::where('user_id', $userId)
            ->whereIn('payment_status', ['completed', 'paid'])
            ->select('amount', 'created_at')
            ->get();

        foreach ($payments as $payment) {
            $activities->push([
                'title' => 'Payment completed - ' . $payment->amount . ' SAR',
                'time'  => $payment->created_at,
            ]);
        }


        $activities = $activities
            ->sortByDesc('time')
            ->take(10)
            ->values()
            ->map(function ($item) {
                return [
                    'title' => $item['title'],
                    'time'  => Carbon::parse($item['time'])->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }


    public function History(Request $request)
    {
        $userId = $request->user()->id;

        $query = Booking::with([
            'service:id,service_name,duration,price,user_id',
            'service.merchant:id,name,phone,business_category',
            'bookedUser:id,name,image'
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

        $result = $bookings->getCollection()->map(function ($booking) {
            return [
                'booking_id'     => $booking->id,
                'customer_image' => $booking->bookedUser->image ?? null,
                'customer'       => $booking->bookedUser->name ?? null,
                'service_name'   => $booking->service->service_name ?? null,
                'amount'         => $booking->service->price ?? null,
                'booking_date'   => Carbon::parse($booking->date_time)->format('M d, Y'),
                'status'         => ucfirst($booking->status),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage()
            ]
        ]);
    }

    public function show($id)
    {

        $userId = auth()->user()->id;

        $booking = Booking::with([
            'bookedUser:id,name,email,phone,image',
            'service:id,service_name,duration,price',
            'staff:id,name'
        ])
            ->where('id', $id)
            ->where('booking_by', $userId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'No booking found for this user.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'name'  => $booking->bookedUser->name ?? null,
                    'email' => $booking->bookedUser->email ?? null,
                    'phone' => $booking->bookedUser->phone ?? null,
                ],

                'booking' => [
                    'booking_id' => $booking->id,
                    'service'    => $booking->service->service_name ?? null,
                    'date_time'  => Carbon::parse($booking->date_time)->format('M d, Y h:i A'),
                    'duration'   => $booking->service->duration . ' min' ?? null,
                    'staff'      => $booking->staff->name ?? 'Not Assigned',
                    'price'      => $booking->service->price ?? null,
                    'status'     => ucfirst($booking->status),
                ]
            ]
        ]);
    }


    public function paymentHistory(Request $request)
    {
        $userId = auth()->user()->id;

        $query = Booking::with([
            'merchantPayment',
            'merchantStore:id,user_id,store_name,business_logo',
            'merchant:id,name,email'
        ])
            ->where('booking_by', $userId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('merchantStore', fn($mq) => $mq->where('store_name', 'LIKE', "%$search%"))
                    ->orWhereHas('merchantPayment', fn($pq) => $pq->where('transaction_id', 'LIKE', "%$search%"))
                    ->orWhereHas('merchant', fn($mq) => $mq->where('name', 'LIKE', "%$search%"))
                    ->orWhere('id', 'LIKE', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->whereHas('merchantPayment', fn($pq) => $pq->where('payment_status', $request->status));
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $payments->getCollection()->map(function ($booking) {
            $payment = $booking->merchantPayment;

            $paymentMethod = match ($payment->payment_method ?? 3) {
                0 => 'Credit Card',
                1 => 'Paypal',
                2 => 'Pay at Store',
                3 => 'Cash',
                default => 'N/A'
            };

            return [
                'tx_id'          => $payment->transaction_id ?? null,
                'merchant_name'  => $booking->merchant->name ?? null,
                'business_logo'  => $booking->merchantStore->business_logo ?? null,
                'business_name'  => $booking->merchantStore->store_name ?? null,
                'date'           => Carbon::parse($booking->created_at)->format('M d, Y'),
                'amount'         => $payment->amount ?? 0,
                'payment_method' => $paymentMethod,
                'status'         => ucfirst($payment->payment_status ?? $booking->status),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total'        => $payments->total(),
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
                'per_page'     => $payments->perPage()
            ]
        ]);
    }


    public function showPayment($bookingId)
    {

        $userId = auth()->user()->id;

        $booking = Booking::with([
            'merchantPayment',
            'merchantStore:id,user_id,store_name,business_logo',
            'merchant:id,name,email,phone'
        ])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'No payment information found for this user. Please check your booking ID.',
            ], 404);
        }

        $payment = $booking->merchantPayment;

        $paymentMethod = match ($payment->payment_method ?? 3) {
            0 => 'Credit Card',
            1 => 'Paypal',
            2 => 'Pay at Store',
            3 => 'Cash',
            default => 'N/A'
        };


        $data = [
            'payment_status' => ucfirst($payment->payment_status ?? $booking->status),
            'transaction_info' => [
                'transaction_id' => $payment->transaction_id ?? '#TX' . str_pad($booking->id, 3, '0', STR_PAD_LEFT),
                'amount'         => $payment->amount ?? 0,
                'date_time'      => Carbon::parse($payment->paid_at ?? $booking->created_at)->format('M d, Y h:i A'),
                'payment_method' => $paymentMethod,
            ],
            'customer_info' => [
                'merchant_name'  => $booking->merchant->name ?? null,
                'business_name'  => $booking->merchantStore->store_name ?? null,
                'email'          => $booking->merchant->email ?? null,
                'phone'          => $booking->merchant->phone ?? null,
            ]
        ];


        return response()->json([
            'success' => true,
            'data'    => $data
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
            'merchantPayment'
        ])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'No booking found for this user. Please check your booking ID and try again.',
            ], 404);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->service->user_id)
            ->first();

        if (!$storeSetting || !$storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone not set.',
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow     = Carbon::now($merchantTimeZone);

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
                'location'      => $booking->merchantStore->business_address ?? null,
                'phone'         => $booking->merchant->phone ?? null,
            ],
            'booking_info' => [
                'booking_id'    => $booking->id,
                'service_name'  => $booking->service->service_name ?? null,

                'date_time'     => $bookingDateTime->format('M d, Y h:i A'),

                'duration'      => $booking->service->duration ?? null,
                'staff_name'    => $booking->staff->name ?? 'Not Assigned',
                'price'         => $booking->service->price ?? 0,
                'payment_status' => $paymentStatus,
            ]
        ];

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }


    public function cancelPreview(Request $request, $bookingId)
    {
        $userId = $request->user()->id;

        $booking = Booking::with(['service'])
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (!$booking) {
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

        if (!$storeSetting || !$storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone not set.',
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow     = Carbon::now($merchantTimeZone);

        if ($bookingDateTime->lt($merchantNow)) {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already expired.',
            ], 400);
        }

        $data = [
            'service_name' => $booking->service->service_name ?? 'N/A',
            'booking_date' => $bookingDateTime->format('M d, Y'),
            'booking_time' => $bookingDateTime->format('h:i A'),
            'note' => 'Cancellation policies may apply. Please check with the merchant for refund details.'
        ];

        return response()->json([
            'success' => true,
            'message' => 'Booking cancellation preview. Please review the details before proceeding.',
            'data' => $data
        ]);
    }


    public function cancelBooking(Request $request, $bookingId)
    {
        $userId = $request->user()->id;

        $booking = Booking::with('merchantPayment')
            ->where('id', $bookingId)
            ->where('booking_by', $userId)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'No booking found for this user. Please check your booking ID and try again.',
            ], 404);
        }

        if ($booking->status === 'cancel') {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already been cancelled.'
            ], 400);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->user_id)
            ->first();

        if (!$storeSetting || !$storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone is not set.'
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow     = Carbon::now($merchantTimeZone);

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
                'message' => 'You cannot cancel this booking within 2 hours of the scheduled time.'
            ], 403);
        }

        $booking->update([
            'status' => 'cancel'
        ]);

        if ($booking->merchantPayment) {
            $booking->merchantPayment->update([
                'payment_status' => 'failed'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your booking has been cancelled successfully.'
        ]);
    }


    public function reschedulePreview(Request $request, $bookingId)
    {
        $booking = Booking::with(['service', 'staff'])
            ->where('id', $bookingId)
            ->where('booking_by', auth()->id())
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }

        if ($booking->status === 'cancel') {
            return response()->json([
                'success' => false,
                'message' => 'Cancelled booking cannot be rescheduled.'
            ], 400);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->service->user_id)
            ->first();

        if (!$storeSetting || !$storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone not set.',
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow     = Carbon::now($merchantTimeZone);

        if ($bookingDateTime->lt($merchantNow)) {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already expired. Expired booking cannot be rescheduled.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'booking_id'   => $booking->id,
                'service'      => $booking->service->service_name ?? null,
                'current_date' => $bookingDateTime->format('M d, Y'),
                'current_time' => $bookingDateTime->format('h:i A'),
                'staff'        => $booking->staff->name ?? 'Any staff'
            ]
        ]);
    }


    public function rescheduleBooking(Request $request, $bookingId)
    {
        $request->validate([
            'date'     => 'required|date',
            'time'     => 'required',
            'staff_id' => 'nullable|integer'
        ]);

        $booking = Booking::where('id', $bookingId)
            ->where('booking_by', auth()->id())
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }

        if ($booking->status === 'cancel') {
            return response()->json([
                'success' => false,
                'message' => 'Cancelled booking cannot be rescheduled.'
            ], 400);
        }

        $storeSetting = DB::table('merchant_store_settings')
            ->where('user_id', $booking->user_id)
            ->first();

        if (!$storeSetting || !$storeSetting->time_zone) {
            return response()->json([
                'success' => false,
                'message' => 'Store timezone is not set.'
            ], 400);
        }

        $merchantTimeZone = $storeSetting->time_zone;

        $bookingDateTime = Carbon::parse($booking->date_time, $merchantTimeZone);
        $merchantNow     = Carbon::now($merchantTimeZone);

        if ($bookingDateTime->lt($merchantNow)) {
            return response()->json([
                'success' => false,
                'message' => 'This booking has already expired. Expired booking cannot be rescheduled.',
            ], 400);
        }

        $newDateTime = Carbon::parse($request->date . ' ' . $request->time, $merchantTimeZone);

        if ($newDateTime->lte($merchantNow)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time is in the past.'
            ], 400);
        }

        if ($merchantNow->diffInMinutes($bookingDateTime, false) <= 120) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot reschedule within 2 hours of the booked time.'
            ], 403);
        }

        $staff = null;

        if ($request->filled('staff_id')) {

            $staff = Staff::where('id', $request->staff_id)
                ->where('user_id', $booking->user_id)
                ->where('status', 1)
                ->first();

            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid staff selected.'
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

            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available staff for selected time.'
                ], 409);
            }
        }

        $booking->update([
            'date_time'      => $newDateTime,
            'staff_id'       => $staff->id,
            'status'         => 'rescheduled',
            'rescheduled_at' => $merchantNow
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking rescheduled successfully.'
        ]);
    }
}
