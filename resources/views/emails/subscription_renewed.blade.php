<!DOCTYPE html>
<html>
<head>
    <title>Subscription Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

    <h2>Hello,</h2>

    @if($isSuccess)
        <p>Good news! Your subscription (ID: #{{ $subscription->id }}) has been successfully renewed automatically.</p>
        <p><strong>Next Renewal Date:</strong> {{ \Carbon\Carbon::parse($subscription->ends_at)->format('M d, Y') }}</p>
        <p>Thank you for staying with us!</p>
    @else
        <p style="color: red;">We were unable to process the auto-renewal payment for your subscription (ID: #{{ $subscription->id }}).</p>
        <p>Please log in to your account and update your payment details to avoid service interruption.</p>
    @endif

    <br>
    <p>Best Regards,<br>Your Company Team</p>

</body>
</html>
