<h2>New Booking Received</h2>

<p><strong>Customer Name:</strong> {{ $booking->customer_name }}</p>

<p><strong>Service:</strong> {{ $booking->service->service_name }}</p>

<p><strong>Date & Time:</strong> {{ $booking->date_time }}</p>

<p><strong>Phone:</strong> {{ $booking->phone }}</p>

<p><strong>Payment Method:</strong> {{ $booking->merchantPayment->payment_method }}</p>

<p>Please check your dashboard for details.</p>
