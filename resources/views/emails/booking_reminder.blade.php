<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>تذكير بالحجز</title>

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

        .details-box {
            margin-top: 25px;
            margin-bottom: 25px;
            background: #f1f7ff;
            border-radius: 8px;
            padding: 20px;
            text-align: right;
        }

        .details-table td {
            padding: 6px 0;
            font-size: 14px;
            color: #555;
            vertical-align: top;
            word-break: break-word;
        }

        .status-badge {
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
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
            margin-top: 12px;
        }

        @media only screen and (max-width: 600px) {

            .container {
                width: 100% !important;
            }

            .content {
                padding: 20px !important;
            }

            .details-box {
                padding: 15px !important;
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

<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f6f8">
    <tr>
        <td align="center" style="padding:20px;">

            <!-- Main Container -->
            <table
                class="container"
                width="100%"
                cellpadding="0"
                cellspacing="0"
            >

                <!-- Logo -->
                <tr>
                    <td align="center" style="padding:20px;">
                        <img
                            src="{{ $message->embed(public_path('logo.png')) }}"
                            width="140"
                            style="width:140px; max-width:140px;"
                            alt="Logo"
                        >
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td class="content">

                        <h2>تذكير بالحجز</h2>

                        <p>
                            مرحبًا <strong>{{ $booking->user->name }}</strong>،
                        </p>

                        @if($type == '24 hours')
                            <p>
                                تم تحديد موعد حجزك خلال
                                <strong>24 ساعة</strong>.
                            </p>
                        @else
                            <p>
                                يبدأ حجزك خلال
                                <strong>1 ساعة</strong>.
                                يرجى الوصول في الموعد المحدد.
                            </p>
                        @endif

                        <!-- Details Box -->
                        <div class="details-box">

                            <h4 style="margin:0 0 15px 0; color:#2d89ef;">
                                تفاصيل الحجز:
                            </h4>

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                class="details-table"
                            >

                                <tr>
                                    <td width="45%">
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
                                        <strong>الحالة:</strong>
                                    </td>

                                    <td>
                                        <span class="status-badge">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                </tr>

                                @if($booking->service)
                                <tr>
                                    <td>
                                        <strong>الخدمة:</strong>
                                    </td>

                                    <td dir="ltr">
                                        {{ $booking->service->service_name }}
                                    </td>
                                </tr>
                                @endif

                            </table>

                        </div>

                        <p style="font-size:14px;">
                            نشكرك لاختيارك خدمتنا!
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td class="footer">

                        إذا لم تقم أنت بإجراء هذا الحجز،
                        فيرجى الاتصال بفريق الدعم لدينا.

                        <br><br>

                        © {{ date('Y') }}

                        <a
                            href="https://bokli.io"
                            style="color:#2d89ef; text-decoration:none;"
                        >
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
