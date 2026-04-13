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
            text-align: left;
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

<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h2 style="margin:0;">New Demo Request!</h2>
            </div>
            <div class="content">
                <p>Hello Team,</p>
                <p>A new demo request has been submitted from the <strong>Bokli.io</strong> website. Here are the lead
                    details:</p>

                <table class="table">
                    <tr>
                        <th>Status</th>
                        <td><span class="badge">New Lead</span></td>
                    </tr>
                    <tr>
                        <th>Full Name</th>
                        <td>{{ $demo->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><a href="mailto:{{ $demo->email }}">{{ $demo->email }}</a></td>
                    </tr>
                    <tr>
                        <th>Business Name</th>
                        <td>{{ $demo->business_name }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $demo->phone }}</td>
                    </tr>

                </table>

                <p style="margin-top: 30px;">Please follow up with this lead as soon as possible.</p>
            </div>
            <div class="footer">
                <p>Sent from <a href="https://bokli.io" style="color:#2d89ef; text-decoration:none;">Bokli.io</a> Internal Notification System</p>
                <p>{{ now()->format('F d, Y h:i A') }}</p>
            </div>
        </div>
    </div>
</body>

</html>
