<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 0 auto;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            text-align: center;
        }
        p {
            line-height: 1.6;
        }
        .details {
            margin-top: 20px;
        }
        .details p {
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }
        .highlight {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Booking Confirmed</h2>

        <p>Hi <span class="highlight">{{ $booking->customer_name }}</span>,</p>
        <p>Your booking has been successfully confirmed. Here are the details:</p>

        <div class="details">
            <p><span class="highlight">Booking ID:</span> BOK{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</p>
            <p><span class="highlight">Service:</span> {{ $booking->service->service_name }}</p>
            <p><span class="highlight">Staff:</span> {{ $booking->staff->name }}</p>
            <p><span class="highlight">Date & Time:</span> {{ \Carbon\Carbon::parse($booking->date_time)->format('Y-m-d h:i A') }}</p>
            <p><span class="highlight">Amount:</span> {{ $booking->merchantPayment->amount }} SAR</p>
            <p><span class="highlight">Payment Method:</span> {{ ucfirst($booking->merchantPayment->payment_method) }}</p>
            <p><span class="highlight">Payment Status:</span> {{ ucfirst($booking->merchantPayment->payment_status) }}</p>
        </div>

        <p>Thank you for booking with us. We look forward to serving you!</p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
