<!DOCTYPE html>
<html>
<head>
    <title>تم تأكيد الحجز</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif;">

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
                        <td style="padding:30px;">

                            <h2 style="margin:0; color:#333; text-align:center;">
                                🎉 تم تأكيد الحجز
                            </h2>

                            <p style="color:#666; margin-top:15px; text-align:center;">
                                مرحبًا <strong>{{ $booking->customer_name }}</strong>,
                            </p>

                            <p style="color:#666; text-align:center;">
                                تم تأكيد حجزك بنجاح. إليك التفاصيل:
                            </p>

                            <!-- Details Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px; font-size:14px; color:#555;">
                                <tr>
                                    <td style="padding:6px 0;"><strong>رقم الحجز:</strong></td>
                                    <td>BOK{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;"><strong>خدمة:</strong></td>
                                    <td>{{ $booking->service->service_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;"><strong>طاقم عمل:</strong></td>
                                    <td>{{ $booking->staff->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;"><strong>التاريخ والوقت:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($booking->date_time)->format('Y-m-d h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;"><strong>كمية:</strong></td>
                                    <td>{{ $booking->merchantPayment->amount }} SAR</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;"><strong>طريقة الدفع:</strong></td>
                                    <td>{{ ucfirst($booking->merchantPayment->payment_method) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;"><strong>حالة الدفع:</strong></td>
                                    <td>{{ ucfirst($booking->merchantPayment->payment_status) }}</td>
                                </tr>
                            </table>

                            <p style="color:#666; text-align:center; margin-top:25px;">
                                نشكركم على حجزكم معنا. نتطلع إلى خدمتكم!
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
