<!DOCTYPE html>
<html>
<head>
    <title>تم تأكيد الحجز</title>
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

                            <h2 style="margin:0; color:#333;">
                                تم تأكيد الحجز 🎉
                            </h2>

                            <p style="color:#666; margin-top:15px;">
                               مرحبًا {{ $booking->customer_name }}،
                            </p>

                            <p style="color:#666;">
                                تم تأكيد حجزك بنجاح.
                            </p>

                            <!-- Details -->
                            <p style="color:#333; margin-top:20px;">
                                <strong>خدمة:</strong><br>
                                {{ $booking->service->service_name }}
                            </p>

                            <p style="color:#333; margin-top:10px;">
                                <strong>التاريخ والوقت:</strong><br>
                               {{ \Carbon\Carbon::parse($booking->date_time)->format('Y-m-d h:i A') }}
                            </p>

                            <p style="color:#333; margin-top:10px;">
                                <strong>طاقم عمل:</strong><br>
                                {{ $booking->staff->name }}
                            </p>

                            <p style="color:#666; margin-top:20px;">
                                شكراً لاختياركم لنا.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9f9f9; padding:20px; text-align:center; font-size:12px; color:#999;">
                            © {{ date('Y') }} <a href="https://bokli.io" style="color:#2d89ef; text-decoration:none;">Bokli.io</a>. جميع الحقوق محفوظة.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
