<!doctype html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>تم إلغاء الحجز</title>

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
            margin-top: 15px;
        }

        .detail {
            color: #333;
            margin-top: 15px;
            font-size: 15px;
            line-height: 24px;
        }

        .message-box {
            margin-top: 20px;
            padding: 15px;
            background: #fff4f4;
            border-radius: 8px;
            color: #c0392b;
            font-size: 14px;
            line-height: 22px;
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

            p,
            .detail,
            .message-box {
                font-size: 14px !important;
                line-height: 22px !important;
            }
        }
    </style>
</head>

<body dir="rtl">
    <table width="100%" bgcolor="#f4f6f8" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 20px">
                <!-- Main Container -->
                <table class="container" width="100%" cellpadding="0" cellspacing="0">
                    <!-- Header / Logo -->
                    <tr>
                        <td align="center" style="padding: 20px">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" width="140"
                                style="width: 140px; max-width: 140px" alt="Logo" />
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content">
                            <h2>تم إلغاء الحجز ❌</h2>

                            <p>مرحبًا <strong>{{ $booking->customer_name }}</strong>،</p>

                            <p>تم إلغاء حجزك بنجاح.</p>

                            <div class="detail">
                                <strong>الخدمة:</strong>
                                <br />
                                {{ $booking->service->service_name }}
                            </div>

                            <div class="detail">
                                <strong>تاريخ الحجز:</strong>
                                <br />

                                <span dir="ltr" style="direction: ltr; unicode-bidi: embed">
                                    {{ \Carbon\Carbon::parse($booking->date_time)->format('Y-m-d
                                                        h:i A') }}
                                </span>
                            </div>



                            <p style="margin-top: 25px">شكرًا لك.</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            © {{ date('Y') }}

                            <a href="https://bokli.io" style="color: #2d89ef; text-decoration: none">
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
