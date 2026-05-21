<!doctype html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>تحديث الاشتراك</title>

    <style>
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
            }

            .card {
                width: 100% !important;
                border-radius: 0 !important;
            }

            .content {
                padding: 20px !important;
            }

            .text {
                font-size: 15px !important;
                line-height: 1.6 !important;
            }

            .btn {
                width: 100% !important;
                display: block !important;
                text-align: center !important;
            }

            .logo {
                max-height: 40px !important;
            }
        }
    </style>
</head>

<body style="
      margin: 0;
      padding: 0;
      background: #f4f6f8;
      font-family: Arial, sans-serif;
    ">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 20px" class="container">
        <tr>
            <td align="center">
                <table width="500" cellpadding="0" cellspacing="0" class="card"
                    style="
              max-width: 500px;
              width: 100%;
              background: #ffffff;
              border-radius: 10px;
              overflow: hidden;
              box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            ">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding: 20px">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" class="logo"
                                style="max-height: 50px" />
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content" style="padding: 30px; text-align: center; direction: rtl">
                            @if ($isSuccess)
                                <h2 class="text" style="margin: 0; color: #28a745; font-size: 20px">
                                    تم تجديد الاشتراك بنجاح 🎉
                                </h2>

                                <p class="text" style="color: #666; margin-top: 10px">
                                    خبر سار! تم تجديد اشتراكك (رقم: #{{ $subscription->id }})
                                    تلقائيًا بنجاح.
                                </p>

                                <table width="100%"
                                    style="
                    margin-top: 20px;
                    text-align: right;
                    font-size: 14px;
                    color: #555;
                  ">
                                    <tr>
                                        <td><strong>تاريخ التجديد القادم:</strong></td>
                                        <td>
                                            <span dir="ltr" style="direction: ltr; unicode-bidi: embed">
                                                {{ \Carbon\Carbon::parse($subscription->ends_at)->format('M
                                                                        d, Y') }}</span>
                                        </td>
                                    </tr>
                                </table>

                                <p class="text" style="margin-top: 20px; color: #666">
                                    شكرًا لثقتك بنا!
                                </p>
                            @else
                                <h2 class="text" style="margin: 0; color: #e74c3c; font-size: 20px">
                                    فشل الدفع ⚠️
                                </h2>

                                <p class="text" style="color: #666; margin-top: 10px">
                                    لم نتمكن من معالجة الدفع التلقائي لتجديد اشتراكك (رقم: #{{ $subscription->id }}).
                                </p>

                                <p class="text" style="color: #666">
                                    يرجى تحديث بيانات الدفع الخاصة بك لتجنب انقطاع الخدمة.
                                </p>

                                <a href="{{ $url ?? '#' }}" class="btn"
                                    style="
                    display: inline-block;
                    margin-top: 20px;
                    padding: 12px 25px;
                    background: #e74c3c;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 6px;
                  ">
                                    تحديث الدفع
                                </a>
                            @endif
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
                  direction: rtl;
                ">
                            إذا لم تطلب هذا، يمكنك تجاهل هذه الرسالة بأمان.
                            <br /><br />
                            © {{ date('Y') }}
                            <a href="https://bokli.io" style="color: #2d89ef; text-decoration: none">Bokli.io</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
