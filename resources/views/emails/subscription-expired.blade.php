<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Subscription Expired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5; /* Bokli Brand Color */
            text-decoration: none;
        }
        .content {
            padding: 20px 0;
            line-height: 1.6;
            color: #333333;
        }
        .warning-text {
            color: #dc2626;
            font-weight: bold;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777777;
            border-top: 2px solid #f4f4f4;
            padding-top: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <a href="https://bokli.io" class="logo">Bokli.io</a>
    </div>

    <div class="content">
        <p>Dear {{ $user->name }},</p>

        <p>We wanted to inform you that your automatic subscription renewal on <strong>Bokli.io</strong> could not be processed, and your subscription has officially <span class="warning-text">expired</span>.</p>

        <p>This usually happens due to insufficient funds, an expired credit card, or your bank declining the automated transaction via our payment gateway.</p>

        <p>To prevent any service interruption to your booking platform and retain access to your store settings, please log in to your dashboard and update your payment details or choose a new plan manually.</p>

        <div class="button-container">
            <a href="{{ env('FRONTEND_URL', 'https://bokli.io') }}/login" class="btn">Renew Subscription Now</a>
        </div>

        <p>If you have any questions or need assistance, please reply directly to this email or reach out to our support team.</p>

        <p>Best regards,<br>The Bokli.io Team</p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Bokli.io. All rights reserved.<br>
        Saudi Arabia, Riyadh
    </div>
</div>

</body>
</html>
