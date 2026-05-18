<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>تم تأكيد الحجز</title>

    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #f4f6f8;
            font-family: Arial, sans-serif;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        img {
            border: 0;
            display: block;
            max-width: 100%;
            height: auto;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        .content {
            padding: 30px;
        }

        .details td {
            padding: 6px 0;
            font-size: 14px;
            color: #555;
        }

        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
            }

            .content {
                padding: 20px !important;
            }

            h2 {
                font-size: 22px !important;
                line-height: 30px !important;
            }

            p,
            td {
                font-size: 14px !important;
                line-height: 22px !important;
            }
        }
    </style>
</head>

<body dir="rtl">

    <table width="100%" bgcolor="#f4f6f8" style="padding:20px;">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table class="container" width="100%" cellpadding="0" cellspacing="0">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" width="140"
                                style="width:140px; max-width:140px;">
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content" style="text-align:center;">

                            <h2 style="margin:0; color:#333;">
                                تم تأكيد الحجز 🎉
                            </h2>

                            <p style="color:#666; margin-top:15px;">
                                مرحبًا <strong>{{ $booking->customer_name }}</strong>،
                            </p>

                            <p style="color:#666;">
                                تم تأكيد حجزك بنجاح. إليك التفاصيل:
                            </p>

                            <!-- Details Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" class="details"
                                style="margin-top:20px; text-align:right;">

                                <tr>
                                    <td><strong>رقم الحجز:</strong></td>
                                    <td>BOK{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</td>
                                </tr>

                                <tr>
                                    <td><strong>خدمة:</strong></td>
                                    <td>{{ $booking->service->service_name }}</td>
                                </tr>

                                <tr>
                                    <td><strong>طاقم عمل:</strong></td>
                                    <td>{{ $booking->staff->name }}</td>
                                </tr>

                                <tr>
                                    <td><strong>التاريخ والوقت:</strong></td>
                                    <td>
                                        <span dir="ltr" style="direction:ltr; unicode-bidi:embed;">
                                            {{ \Carbon\Carbon::parse($booking->date_time)->format('Y-m-d h:i A') }}
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>المبلغ:</strong></td>
                                    <td>{{ $booking->merchantPayment->amount }} SAR</td>
                                </tr>

                                <tr>
                                    <td><strong>طريقة الدفع:</strong></td>
                                    <td>{{ ucfirst($booking->merchantPayment->payment_method) }}</td>
                                </tr>

                                <tr>
                                    <td><strong>حالة الدفع:</strong></td>
                                    <td>{{ ucfirst($booking->merchantPayment->payment_status) }}</td>
                                </tr>

                            </table>

                            <p style="color:#666; margin-top:25px;">
                                نشكركم على حجزكم معنا. نتطلع إلى خدمتكم!
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9f9f9; padding:20px; text-align:center; font-size:12px; color:#999;">
                            © {{ date('Y') }}
                            <a href="https://bokli.io" style="color:#2d89ef; text-decoration:none;">
                                Bokli.io
                            </a>
                            . جميع الحقوق محفوظة.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
