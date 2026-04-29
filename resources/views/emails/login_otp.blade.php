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
<body dir="rtl">
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
        </div>
        <div class="content">
            <p>مرحبًا،</p>
            <p>كلمة المرور لمرة واحدة (OTP) الخاصة بك لتسجيل الدخول إلى حسابك هي:</p>
            <div class="otp-code">{{ $otp }}</div>
            <p>هذا الرمز صالح لـ <strong>5 دقائق</strong>. يرجى عدم مشاركة هذا الرمز مع أي شخص.</p>
            <p>إذا لم تحاول تسجيل الدخول، فيرجى تجاهل هذه الرسالة الإلكترونية أو تأمين حسابك.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} <a href="https://yourcompany.com">Bokli.io</a>. جميع الحقوق محفوظة.
        </div>
    </div>
</body>
</html>
