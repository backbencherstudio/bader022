<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تحقق من بريدك الإلكتروني</title>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #f1f5f9;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        max-width: 600px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .header {
        /* background: linear-gradient(135deg, #4f46e5, #7c3aed); */
        padding: 25px;
        text-align: center;
    }

    .header img {
        max-height: 50px;
    }

    .content {
        padding: 40px 30px;
        text-align: center;
        color: #1f2937;
    }

    .content h2 {
        margin-bottom: 10px;
        font-size: 24px;
    }

    .content p {
        font-size: 15px;
        color: #6b7280;
        line-height: 1.6;
    }

    .otp-box {
        margin: 30px 0;
        padding: 18px;
        font-size: 34px;
        font-weight: bold;
        letter-spacing: 8px;
        color: #4f46e5;
        background: #eef2ff;
        border-radius: 10px;
        border: 2px dashed #4f46e5;
        display: inline-block;
    }

    .expiry {
        font-size: 14px;
        color: #374151;
        margin-top: 10px;
    }

    .button {
        margin-top: 25px;
    }

    .button a {
        text-decoration: none;
        background: #4f46e5;
        color: #ffffff;
        padding: 12px 25px;
        border-radius: 8px;
        font-size: 14px;
        display: inline-block;
    }

    .warning {
        margin-top: 25px;
        font-size: 13px;
        color: #ef4444;
    }

    .footer {
        background: #f9fafb;
        padding: 20px;
        text-align: center;
        font-size: 12px;
        color: #9ca3af;
    }

    .footer a {
        color: #4f46e5;
        text-decoration: none;
        font-weight: 500;
    }

    @media (max-width: 600px) {
        .content {
            padding: 30px 20px;
        }

        .otp-box {
            font-size: 26px;
            letter-spacing: 5px;
        }
    }
</style>
</head>

<body dir="rtl">

<div class="container">

    <div class="header">
        <img src="{{ $message->embed(public_path('logo.png')) }}">
    </div>

    <div class="content">
        <h2>التحقق من البريد الإلكتروني</h2>

        <p>
            استخدم رمز التحقق لمرة واحدة (OTP) أدناه لإكمال عملية التسجيل.
        </p>

        <div class="otp-box">
            {{ $otp }}
        </div>

        <div class="expiry">
            ⏳ سينتهي صلاحية هذا الرمز في <strong>5 دقائق</strong>
        </div>

        <!-- Optional Button (if you add link verification later) -->
        <!--
        <div class="button">
            <a href="#">Verify Now</a>
        </div>
        -->

        <p class="warning">
            إذا لم تطلب ذلك، يمكنك تجاهل هذه الرسالة الإلكترونية بأمان.
        </p>
    </div>

    <div class="footer">
        © {{ date('Y') }}
        <a href="https://bokli.io" target="_blank">Bokli.io</a>
        جميع الحقوق محفوظة.
    </div>

</div>

</body>
</html>
