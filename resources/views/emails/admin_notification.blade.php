<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .header {
            background: #1e293b;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            text-align: right;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background-color: #f8fafc;
            color: #64748b;
            width: 35%;
        }

        .badge {
            background: #dcfce7;
            color: #166534;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body dir="rtl">
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h2 style="margin:0;">طلب عرض توضيحي جديد!</h2>
            </div>
            <div class="content">
                <p>مرحباً بالفريق،</p>
                <p>تم تقديم طلب عرض توضيحي جديد من <strong>Bokli.io</strong> الموقع الإلكتروني. إليكم أبرز النقاط
                    تفاصيل:</p>
                <table class="table">
                    <tr>
                        <th>حالة</th>
                        <td><span class="badge">عميل جديد</span></td>
                    </tr>
                    <tr>
                        <th>الاسم الكامل</th>
                        <td>{{ $demo->name }}</td>
                    </tr>
                    <tr>
                        <th>بريد إلكتروني</th>
                        <td><a href="mailto:{{ $demo->email }}">{{ $demo->email }}</a></td>
                    </tr>
                    <tr>
                        <th>اسم الشركة</th>
                        <td>{{ $demo->business_name }}</td>
                    </tr>
                    <tr>
                        <th>هاتف</th>
                        <td>{{ $demo->phone }}</td>
                    </tr>

                </table>

                <p style="margin-top: 30px;">يرجى متابعة هذا الأمر في أقرب وقت ممكن.</p>
            </div>
            <div class="footer">
                <p>تم الإرسال من <a href="https://bokli.io" style="color:#2d89ef; text-decoration:none;">Bokli.io</a> نظام الإخطار الداخلي</p>
                <p>{{ now()->format('F d, Y h:i A') }}</p>
            </div>
        </div>
    </div>
</body>

</html>
