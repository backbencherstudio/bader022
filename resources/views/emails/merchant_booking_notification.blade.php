<!doctype html>
<html>

<head>
    <title></title>
</head>

<body dir="rtl"
    style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif; direction:rtl; text-align:right;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 20px">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="500" cellpadding="0" cellspacing="0"
                    style="
              background: #ffffff;
              border-radius: 10px;
              overflow: hidden;
              box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            ">
                    <!-- Header / Logo -->
                    <tr>
                        <td align="center" style="padding: 20px">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height: 50px" />
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px">
                            <h2 style="margin: 0; color: #333; text-align: center">
                                تم استلام حجز جديد
                            </h2>

                            <!-- Details Box -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="margin-top:20px; font-size:14px; color:#555; direction:rtl; text-align:right;">
                                
                                <tr>
                                    <td style="padding: 6px 0"><strong>رقم الحجز:</strong></td>
                                    <td dir="ltr">
                                        BOK{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 6px 0">
                                        <strong>اسم العميل:</strong>
                                    </td>
                                    <td dir="ltr">{{ $booking->customer_name }}</td>
                                </tr>

                                <tr>
                                    <td style="padding: 6px 0"><strong>فرع:</strong></td>
                                    <td dir="ltr">{{ $booking->branch->name ?? 'N/A' }}</td>
                                </tr>

                                <tr>
                                    <td style="padding: 6px 0"><strong>خدمة:</strong></td>
                                    <td dir="ltr">{{ $booking->service->service_name }}</td>
                                </tr>

                                <tr>
                                    <td style="padding: 6px 0">
                                        <strong>التاريخ والوقت:</strong>
                                    </td>
                                    <td dir="ltr">
                                        {{ \Carbon\Carbon::parse($booking->date_time)->format('Y-m-d
                                                              h:i A') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 6px 0"><strong>المبلغ:</strong></td>
                                    <td dir="ltr">{{ $booking->merchantPayment->amount ?? '0' }} SAR</td>
                                </tr>

                                <tr>
                                    <td style="padding: 6px 0">
                                        <strong>طريقة الدفع:</strong>
                                    </td>
                                    <td dir="ltr">
                                        {{ ucfirst($booking->merchantPayment->payment_method ?? 'N/A') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 6px 0">
                                        <strong>حالة الدفع:</strong>
                                    </td>
                                    <td dir="ltr">
                                        {{ ucfirst($booking->merchantPayment->payment_status ?? 'Pending') }}
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #666; text-align: center; margin-top: 25px">
                                يرجى مراجعة لوحة التحكم الخاصة بك لمزيد من التفاصيل.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="
                  background: #f9f9f9;
                  padding: 20px;
                  text-align: center;
                  font-size: 12px;
                  color: #999;
                ">
                            © {{ date('Y') }}
                            <a href="https://bokli.io" style="color: #2d89ef; text-decoration: none">
                                Bokli.io</a>. جميع الحقوق محفوظة.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
