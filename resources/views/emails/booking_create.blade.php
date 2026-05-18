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
            text-align: center;
        }

        .footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        h2 {
            margin: 0;
            color: #333;
            font-size: 26px;
            line-height: 36px;
        }

        p {
            color: #666;
            font-size: 16px;
            line-height: 26px;
            margin-top: 10px;
        }

        .detail {
            color: #333;
            margin-top: 12px;
            font-size: 15px;
            line-height: 24px;
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
            .detail {
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
                        <td class="content">

                            <h2>تم تأكيد الحجز 🎉</h2>

                            <p>
                                مرحبًا <strong>{{ $booking->customer_name }}</strong>،
                            </p>

                            <p>
                                تم تأكيد حجزك بنجاح.
                            </p>

                            <!-- Details -->
                            <div class="detail">
                                <strong>خدمة:</strong><br>
                                {{ $booking->service->service_name }}
                            </div>

                            <div class="detail">
                                <strong>التاريخ والوقت:</strong><br>
                                <span dir="ltr" style="direction:ltr; unicode-bidi:embed;">
                                    {{ \Carbon\Carbon::parse($booking->date_time)->format('Y-m-d h:i A') }}
                                </span>
                            </div>

                            <div class="detail">
                                <strong>طاقم عمل:</strong><br>
                                {{ $booking->staff->name }}
                            </div>

                            <p style="margin-top:20px;">
                                شكراً لاختياركم لنا.
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
