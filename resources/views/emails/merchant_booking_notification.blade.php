<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>تم استلام حجز جديد</title>

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
            padding: 8px 0;
            font-size: 14px;
            color: #555;
            vertical-align: top;
            word-break: break-word;
        }

        .footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
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

            td,
            p {
                font-size: 14px !important;
                line-height: 22px !important;
            }
        }
    </style>
</head>

<body dir="rtl">

    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f6f8">
        <tr>
            <td align="center" style="padding:20px;">

                <!-- Main Container -->
                <table class="container" width="100%" cellpadding="0" cellspacing="0">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" width="140"
                                style="width:140px; max-width:140px;" alt="Logo">
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content">

                            <h2 style="margin:0; color:#333; text-align:center;">
                                تم استلام حجز جديد
                            </h2>

                            <!-- Details -->
                            <table width="100%" cellpadding="0" cellspacing="0" class="details"
                                style="margin-top:20px; text-align:right;">

                                <tr>
                                    <td width="40%">
                                        <strong>رقم الحجز:</strong>
                                    </td>

                                    <td dir="ltr">
                                        BOK{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>اسم العميل:</strong>
                                    </td>

                                    <td dir="ltr">
                                        {{ $booking->customer_name }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>فرع:</strong>
                                    </td>

                                    <td dir="ltr">
                                        {{ $booking->branch->name ?? 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>خدمة:</strong>
                                    </td>

                                    <td dir="ltr">
                                        {{ $booking->service->service_name }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>التاريخ والوقت:</strong>
                                    </td>

                                    <td>
                                        <span dir="ltr" style="direction:ltr; unicode-bidi:embed;">
                                            {{ \Carbon\Carbon::parse($booking->date_time)->format('Y-m-d h:i A') }}
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>المبلغ:</strong>
                                    </td>

                                    <td dir="ltr">
                                        {{ $booking->merchantPayment->amount ?? '0' }} SAR
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>طريقة الدفع:</strong>
                                    </td>

                                    <td dir="ltr">
                                        {{ ucfirst($booking->merchantPayment->payment_method ?? 'N/A') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>حالة الدفع:</strong>
                                    </td>

                                    <td dir="ltr">
                                        {{ ucfirst($booking->merchantPayment->payment_status ?? 'Pending') }}
                                    </td>
                                </tr>

                            </table>

                            <p style="color:#666; text-align:center; margin-top:25px;">
                                يرجى مراجعة لوحة التحكم الخاصة بك لمزيد من التفاصيل.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
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
