<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            /* background-color: #4f46e5; */
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
            text-align: center;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: 5px;
            margin: 20px 0;
            padding: 10px;
            background-color: #f3f4f6;
            display: inline-block;
            border-radius: 4px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>Merchant Registration OTP</p>
            <div class="otp-code">{{ $otp }}</div>
            <p>This code is valid for <strong>5 minutes</strong>. Please do not share this code with anyone.</p>
            <p>If you did not request this registration, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} <a href="https://yourcompany.com">Bokli.io</a>. All rights reserved.
        </div>
    </div>
</body>
</html>
