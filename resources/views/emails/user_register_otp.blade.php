<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration OTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .header {
            /* background-color: #4f46e5; Indigo color */
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 40px;
            text-align: center;
            color: #333333;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #4f46e5;
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
            border: 1px dashed #4f46e5;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .warning {
            color: #ef4444;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
        </div>

        <div class="content">
            <h3>Hello!</h3>
            <p>Thank you for choosing us. Use the following OTP to complete your registration process.</p>

            <div class="otp-code">
                {{ $otp }}
            </div>

            <p>This code is valid for <strong>5 minutes</strong>.</p>

            <p class="warning">
                If you did not request this code, please ignore this email.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} <a href="https://bokli.io" target="_blank">Bokli.io</a> All rights reserved.</p>
        </div>
    </div>
</body>
</html>
