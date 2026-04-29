<!DOCTYPE html>
<html>
<head>
    <title>رمز التحقق لإعادة تعيين كلمة المرور</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05);">

                    <!-- Header / Logo -->
                    <tr>
                        <td align="center" style=" padding:20px;">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:30px; text-align:center;">

                            <h2 style="margin:0; color:#333;">إعادة تعيين كلمة المرور</h2>

                            <p style="color:#666; margin-top:10px;">
                                استخدم رمز التحقق لمرة واحدة (OTP) أدناه لإعادة تعيين كلمة مرورك
                            </p>

                            <!-- OTP Box -->
                            <div style="margin:25px 0;">
                                <span style="display:inline-block; padding:15px 25px; font-size:28px; letter-spacing:6px; background:#f1f7ff; color:#2d89ef; border-radius:8px; font-weight:bold;">
                                    {{ $otp }}
                                </span>
                            </div>

                            <p style="color:#666;">
                                ستنتهي صلاحية رمز التحقق هذا في غضون <b>5 دقائق</b>.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9f9f9; padding:20px; text-align:center; font-size:12px; color:#999;">
                            إذا لم تطلب ذلك، يمكنك تجاهل هذه الرسالة الإلكترونية بأمان.
                            <br><br>
                            © {{ date('Y') }} <a href="https://bokli.io" style="color:#2d89ef; text-decoration:none;">Bokli.io</a>. جميع الحقوق محفوظة.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
