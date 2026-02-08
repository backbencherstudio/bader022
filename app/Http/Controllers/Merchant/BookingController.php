<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Booking;
use App\Models\MerchantPayment;
use App\Models\Staff;
use App\Models\Service;
use App\Models\BusinessHour;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $merchant = auth()->user();

        $validator = Validator::make($request->all(), [
            'staff_id'       => 'required|exists:staffs,id',
            'service_id'     => 'required|exists:services,id',
            'customer_name'  => 'required|string|max:255',
            'email'          => 'required|email',
            'phone'          => 'required|string|max:15',
            'date_time'      => 'required|date_format:Y-m-d H:i:s',
            'special_note'   => 'nullable|string|max:255',
            'payment_method' => 'nullable|in:0,1,2,3',
            'amount'         => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $staff = Staff::where('id', $request->staff_id)
            ->where('user_id', $merchant->id)
            ->where('status', 1)
            ->first();

        $service = Service::where('id', $request->service_id)
            ->where('user_id', $merchant->id)
            ->first();

        if (!$staff || !$service) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid staff or service selection'
            ], 403);
        }

        DB::transaction(function () use ($request, $merchant, $service) {

            $booking = Booking::create([
                'user_id'        => $merchant->id,
                'staff_id'       => $request->staff_id,
                'service_id'     => $service->id,
                'customer_name'  => $request->customer_name,
                'email'          => $request->email,
                'phone'          => $request->phone,
                'date_time'      => $request->date_time,
                'status'         => 'pending',
                'special_note'   => $request->special_note,
                'booking_by'     => 'merchant',
                'payment_method' => $request->payment_method ?? 2,
            ]);

            if ($request->has('payment_method')) {
                MerchantPayment::create([
                    'booking_id'     => $booking->id,
                    'user_id'        => $merchant->id,
                    'payment_method' => $request->payment_method,
                    'amount'         => $request->amount ?? $service->price,
                    'payment_status' => 'pending',
                    'transaction_id' => $request->transaction_id ?? null,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully'
        ], 201);
    }


    public function getAvailability(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
            'staff_id'   => 'nullable|integer'
        ]);

        $date = Carbon::parse($request->date);
        $day  = $date->format('l');

        $service = Service::findOrFail($request->service_id);
        $merchantId = $service->user_id;

        $businessHour = BusinessHour::where('user_id', $merchantId)
            ->where('day', $day)
            ->where('is_closed', 0)
            ->first();

        if (!$businessHour) {
            return response()->json([
                'available_times' => [],
                'message' => 'Business closed'
            ]);
        }

        $duration = (int) $service->duration;

        $slots = [];
        $start = Carbon::createFromTimeString($businessHour->open_time);
        $end   = Carbon::createFromTimeString($businessHour->close_time);

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($duration);
        }

        $bookings = Booking::where('user_id', $merchantId)
            ->whereDate('date_time', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($request->staff_id, function ($q) use ($request) {
                $q->where('staff_id', $request->staff_id);
            })
            ->get();

        $availableSlots = [];

        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($request->date . ' ' . $slot);
            $slotEnd   = $slotStart->copy()->addMinutes($duration);

            $overlap = false;

            foreach ($bookings as $booking) {
                $bookingStart = Carbon::parse($booking->date_time);
                $bookingEnd   = $bookingStart->copy()
                    ->addMinutes($booking->service->duration);

                if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                    $overlap = true;
                    break;
                }
            }

            if (!$overlap) {
                $availableSlots[] = $slotStart->format('h:i A');
            }
        }

        return response()->json([
            'available_times' => $availableSlots
        ]);
    }

    public function getStaffByService($serviceId)
    {
        $service = Service::findOrFail($serviceId);

        return Staff::where('user_id', $service->user_id)
            ->where('service_id', $service->id)
            ->where('status', 1)
            ->get();
    }


    public function bookingByUser(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'service_id'    => 'required|exists:services,id',
            'staff_id'      => 'nullable|integer',
            'date'          => 'required|date',
            'time'          => 'required',
            'customer_name' => 'required|string',
            'email'         => 'required|email',
            'phone'         => 'required|string',
            'special_note'  => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $user) {

            $service = Service::findOrFail($request->service_id);
            $merchantId = $service->user_id;
            $duration   = (int) $service->duration;

            $staffId = $request->staff_id;

            if ($staffId) {
                $staff = Staff::where('id', $staffId)
                    ->where('user_id', $merchantId)
                    ->where('service_id', $service->id)
                    ->where('status', 1)
                    ->first();

                if (!$staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid staff selection'
                    ], 422);
                }
            } else {
                $staffId = Staff::where('user_id', $merchantId)
                    ->where('service_id', $service->id)
                    ->where('status', 1)
                    ->inRandomOrder()
                    ->value('id');
            }

            if (!$staffId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No staff available'
                ], 422);
            }

            $slotStart = Carbon::parse($request->date . ' ' . $request->time);
            $slotEnd   = $slotStart->copy()->addMinutes($duration);

            $conflict = Booking::where('user_id', $merchantId)
                ->where('staff_id', $staffId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($q) use ($slotStart, $slotEnd) {
                    $q->where(function ($q2) use ($slotStart, $slotEnd) {
                        $q2->where('date_time', '<', $slotEnd)
                            ->whereRaw(
                                "DATE_ADD(date_time, INTERVAL (SELECT duration FROM services WHERE services.id = bookings.service_id) MINUTE) > ?",
                                [$slotStart]
                            );
                    });
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot is already booked. Please select another time.'
                ], 409);
            }

            $booking = Booking::create([
                'user_id'        => $merchantId,
                'staff_id'       => $staffId,
                'service_id'     => $service->id,
                'customer_name'  => $request->customer_name,
                'email'          => $request->email,
                'phone'          => $request->phone,
                'date_time'      => $slotStart,
                'status'         => 'pending',
                'special_note'   => $request->special_note,
                'booking_by'     => 'user',
                'payment_method' => 2,
            ]);

            MerchantPayment::create([
                'booking_id'     => $booking->id,
                'user_id'        => $merchantId,
                'payment_method' => 2,
                'amount'         => $service->price,
                'transaction_id' => 'PAY-STORE-' . uniqid(),
                'payment_status' => 'pending',
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Booking confirmed. Pay at store.',
                'booking_id' => $booking->id
            ]);
        });
    }
}
