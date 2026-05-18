<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>تم إتمام عملية الدفع بنجاح</title>

    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f4f6f8;
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
        }

        .container {
            width: 100%;
            max-width: 500px;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        .content {
            padding: 30px;
        }

        .details-box {
            background-color: #f9fbff;
            border-radius: 8px;
            padding: 20px;
        }

        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999999;
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

            p {
                font-size: 14px !important;
                line-height: 24px !important;
            }

            .logo img {
                width: 120px !important;
                height: auto !important;
            }
        }
    </style>
</head>

<body dir="rtl">
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f6f8" style="padding: 20px">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table class="container" width="100%" cellpadding="0" cellspacing="0">
                    <!-- Logo -->
                    <tr>
                        <td align="center" class="logo" style="padding: 20px">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" alt="Logo" width="140"
                                style="
                    width: 140px;
                    max-width: 140px;
                    height: auto;
                    display: block;
                  " />
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content" align="center">
                            <h2
                                style="
                    margin: 0;
                    color: #333333;
                    font-size: 28px;
                    line-height: 38px;
                    font-weight: bold;
                  ">
                                تم إتمام عملية الدفع بنجاح 🎉
                            </h2>

                            <p
                                style="
                    color: #666666;
                    margin-top: 15px;
                    font-size: 16px;
                    line-height: 28px;
                  ">
                                عزيزي <strong>{{ $merchant->name }}</strong>،
                                <br />
                                تمت عملية الدفع بنجاح وتم إنشاء حسابك.
                            </p>

                            <!-- Details Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" class="details-box"
                                style="
                    margin-top: 25px;
                    margin-bottom: 25px;
                    background-color: #f9fbff;
                    border-radius: 8px;
                  ">
                                <tr>
                                    <td style="padding: 20px; text-align: right">
                                        <p
                                            style="
                          margin: 8px 0;
                          color: #333333;
                          font-size: 15px;
                        ">
                                            <strong>الاسم:</strong>
                                            {{ $merchant->name }}
                                        </p>

                                        <p
                                            style="
                          margin: 8px 0;
                          color: #333333;
                          font-size: 15px;
                        ">
                                            <strong>اسم الشركة:</strong>
                                            {{ $merchant->business_name }}
                                        </p>

                                        <p
                                            style="
                          margin: 8px 0;
                          color: #333333;
                          font-size: 15px;
                        ">
                                            <strong>فئة الأعمال:</strong>
                                            {{ $merchant->business_category }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p
                                style="
                    color: #666666;
                    font-size: 15px;
                    line-height: 26px;
                    margin: 0;
                  ">
                                نشكركم لاختياركم منصتنا. نتطلع للعمل معكم!
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            إذا لم تطلب ذلك، يمكنك تجاهل هذه الرسالة الإلكترونية بأمان.
                            <br /><br />

                            © {{ date('Y') }}

                            <a href="https://bokli.io"
                                style="
                    color: #2d89ef;
                    text-decoration: none;
                    font-weight: bold;
                  ">
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
