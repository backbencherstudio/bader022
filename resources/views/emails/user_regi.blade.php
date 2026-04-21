<!DOCTYPE html>
<html>
<head>
    <title>تم التسجيل بنجاح</title>
</head>

<body dir="rtl" style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05);">

                    <!-- Header / Logo -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:30px; text-align:center;">

                            <h2 style="margin:0; color:#333;">تم التسجيل بنجاح 🎉</h2>

                            <p style="color:#666; margin-top:10px;">
                                مرحبًا {{ $user->name }}، تم إنشاء حسابك بنجاح.
                            </p>

                            <p style="color:#666;">
                                يمكنك الآن تسجيل الدخول باستخدام عنوان بريدك الإلكتروني وكلمة المرور.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9f9f9; padding:20px; text-align:center; font-size:12px; color:#999;">
                            شكراً لانضمامكم إلينا.
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
