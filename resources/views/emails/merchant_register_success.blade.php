<!DOCTYPE html>
<html>
<head>
    <title>{{ $type == 'otp' ? 'Account Verification' : 'Payment Successful' }}</title>
</head>
<body dir="rtl" style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
<tr>
<td align="center">

<table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05);">

    <!-- Logo -->
    <tr>
        <td align="center" style="padding:20px;">
            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
        </td>
    </tr>

    <!-- Content -->
    <tr>
        <td style="padding:30px; text-align:center;">

            @if($type == 'otp')

                <h2 style="margin:0; color:#28a745;">تم التسجيل بنجاح 🎉</h2>

                <p style="color:#666; margin-top:10px;">
                    تم إنشاء حسابك التجاري بنجاح.
                </p>

                <p style="color:#666;">
                    استخدم رمز التحقق لمرة واحدة (OTP) أدناه للتحقق من حسابك:
                </p>

                <!-- OTP -->
                <div style="margin:25px 0;">
                    <span style="display:inline-block; padding:15px 25px; font-size:28px; letter-spacing:6px; background:#e8f5e9; color:#28a745; border-radius:8px; font-weight:bold;">
                        {{ $otp }}
                    </span>
                </div>

                <p style="color:#666;">
                    ستنتهي صلاحية رمز التحقق هذا في غضون <b>10 دقائق</b>.
                </p>

                @if(isset($url))
                <a href="{{ $url }}" style="display:inline-block; margin-top:20px; padding:12px 25px; background:#28a745; color:#fff; text-decoration:none; border-radius:6px;">
                    التحقق من الحساب
                </a>
                @endif

            @else

                <h2 style="margin:0; color:#2d89ef;">تم إتمام عملية الدفع بنجاح 💳</h2>

                <p style="color:#666; margin-top:10px;">
                    تم إتمام عملية الدفع بنجاح.
                </p>

                <table width="100%" style="margin-top:20px; text-align:left; font-size:14px; color:#555;">
                    <tr>
                        <td><strong>كمية:</strong></td>
                        <td>{{ $amount }} {{ $currency }}</td>
                    </tr>
                    <tr>
                        <td><strong>رقم العملية:</strong></td>
                        <td>{{ $transaction_id }}</td>
                    </tr>
                    @if(isset($plan))
                    <tr>
                        <td><strong>يخطط:</strong></td>
                        <td>{{ $plan }}</td>
                    </tr>
                    @endif
                </table>

                <p style="margin-top:20px; color:#666;">
                    شكراً لشرائك
                </p>

            @endif

        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="background:#f9f9f9; padding:20px; text-align:center; font-size:12px; color:#999;">
            إذا لم تطلب ذلك، يمكنك تجاهل هذه الرسالة الإلكترونية.
            <br><br>
            © {{ date('Y') }}
            <a href="https://bokli.io" style="color:#2d89ef; text-decoration:none;">Bokli.io</a>
        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
