<!DOCTYPE html>
<html dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد طلب العرض التوضيحي</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header {
            /* background-color: #2563eb; SaaS Blue Color */
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .content {
            padding: 40px 30px;
            line-height: 1.6;
            color: #334155;
        }

        .content h2 {
            font-size: 20px;
            color: #1e293b;
            margin-top: 0;
        }

        .content p {
            margin-bottom: 20px;
        }

        .details-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .details-box p {
            margin: 5px 0;
            font-size: 14px;
        }

        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
        </div>

        <div class="content">
            <h2>مرحبًا {{ $demo->name }},</h2>
            <p>
                شكرًا لتواصلكم! لقد تلقينا طلبكم لعرض توضيحي مخصص لمنصتنا. فريقنا متحمس لعرض كيف يمكن لمنصة
                <strong>Bokli</strong> أن تساعدكم في توسيع نطاق أعمالكم.
            </p>

            <p>سيتصل بك أحد متخصصي منتجاتنا خلال الـ 24 ساعة القادمة لتحديد موعد مناسب للجولة التعريفية.</p>

            <p>في هذه الأثناء، لا تتردد في استكشاف موقعنا الإلكتروني لمزيد من التفاصيل.</p>

            <a href="https://bokli.io" class="btn">تفضل بزيارة موقعنا الإلكتروني</a>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Bokli.io. جميع الحقوق محفوظة.</p>
            <p>المملكة العربية السعودية | noreply@bokli.io</p>
        </div>
    </div>
</body>

</html>
