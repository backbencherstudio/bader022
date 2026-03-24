
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
</head>
<body>
    <h1>Dear {{ $merchant->name }},</h1>
    <p>Your payment was successful, and your account has been created successfully!</p>

    <h3>Your Account Details:</h3>
    <ul>
        <li><strong>Name:</strong> {{ $merchant->name }}</li>
        <li><strong>Business Name:</strong> {{ $merchant->business_name }}</li>
        <li><strong>Business Category:</strong> {{ $merchant->business_category }}</li>
    </ul>

    <p>Thank you for choosing our platform! We are excited to work with you.</p>
</body>
</html>
