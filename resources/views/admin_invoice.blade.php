<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ data_get($invoice, 'invoice_info.invoice_no', '') }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #333;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
        }

        .logo {
            max-width: 120px;
            max-height: 80px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            color: #4CAF50;
            margin: 0;
        }

        .section-title {
            margin-top: 25px;
            font-size: 16px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table th {
            background: #f5f5f5;
        }

        .summary {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }

        .summary td {
            border: 1px solid #ddd;
        }

        .total {
            font-weight: bold;
            font-size: 16px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
        }

        .sub-data {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">

        <table class="header-table">
            <tr>

                <td>

                    @if (data_get($invoice, 'merchant_info.business_logo'))
                        <img src="{{ public_path(data_get($invoice, 'merchant_info.business_logo', '')) }}" class="logo">
                    @endif

                    <h2>{{ data_get($invoice, 'merchant_info.business_name', '') }}</h2>

                    <p>
                        Merchant: {{ data_get($invoice, 'merchant_info.merchant_name', '') }} <br>
                        Email: {{ data_get($invoice, 'merchant_info.email', '') }} <br>
                        Phone: {{ data_get($invoice, 'merchant_info.phone', '') }} <br>
                        {{ data_get($invoice, 'merchant_info.business_address', '') }}
                    </p>

                </td>

                <td class="invoice-title">

                    <h1>INVOICE</h1>

                    <p>
                        Invoice No: {{ data_get($invoice, 'invoice_info.invoice_no', '') }} <br>
                        Subscription ID: {{ data_get($invoice, 'invoice_info.subscription_id', '') }} <br>
                        Date: {{ data_get($invoice, 'invoice_info.invoice_date', '') }}
                    </p>

                </td>

            </tr>
        </table>

    </div>


    <div class="section-title">Subscription Details</div>

    <table>

        <tr>
            <th>Package</th>
            <th>Duration</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
        </tr>

        <tr class="sub-data">
            <td>{{ data_get($invoice, 'subscription_details.package', '') }}</td>
            <td>{{ data_get($invoice, 'subscription_details.duration', '') }}</td>
            <td>{{ data_get($invoice, 'subscription_details.start_date', '') }}</td>
            <td>{{ data_get($invoice, 'subscription_details.end_date', '') }}</td>
            <td>{{ data_get($invoice, 'subscription_details.status', '') }}</td>
        </tr>

    </table>


    <div class="section-title">Payment Details</div>

    <table>

        <tr>
            <td>Payment Method</td>
            <td>{{ data_get($invoice, 'payment_details.payment_method', '') }}</td>
        </tr>

        <tr>
            <td>Transaction ID</td>
            <td>{{ data_get($invoice, 'payment_details.transaction_id', '') }}</td>
        </tr>

        <tr>
            <td>Status</td>
            <td>{{ data_get($invoice, 'payment_details.status', '') }}</td>
        </tr>

        <tr>
            <td>Paid At</td>
            <td>{{ data_get($invoice, 'payment_details.paid_at', '') }}</td>
        </tr>

    </table>


    <table class="summary">

        <tr>
            <td>Package Price</td>
            <td>
                {{ data_get($invoice, 'summary.service_price', '0') }}
                {{ data_get($invoice, 'summary.currency', '') }}
            </td>
        </tr>

        <tr>
            <td>Tax</td>
            <td>
                {{ data_get($invoice, 'summary.tax', '0') }}
                {{ data_get($invoice, 'summary.currency', '') }}
            </td>
        </tr>

        <tr>
            <td>Discount</td>
            <td>
                {{ data_get($invoice, 'summary.discount', '0') }}
                {{ data_get($invoice, 'summary.currency', '') }}
            </td>
        </tr>

        <tr class="total">
            <td>Total</td>
            <td>
                {{ data_get($invoice, 'summary.total_amount', '0') }}
                {{ data_get($invoice, 'summary.currency', '') }}
            </td>
        </tr>

    </table>


    <div class="footer">
        Thank you
    </div>

</body>

</html>
