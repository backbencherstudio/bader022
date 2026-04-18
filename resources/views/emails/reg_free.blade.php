<!DOCTYPE html>

<html>
<head>
    <title>Merchant Registration - Free Trial Activated</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
    <tr>
        <td align="center">

            <!-- Main Container -->
            <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05);">

                <!-- Header / Logo -->
                <tr>
                    <td align="center" style="padding:20px;">
                        <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:30px; text-align:center;">

                        <h2 style="margin:0; color:#333;">Merchant Registration</h2>

                        <p style="color:#666; margin-top:10px;">
                            Hello {{ $merchant->name }},
                        </p>

                        <p style="color:#666;">
                            Your merchant account has been successfully created.
                        </p>

                        <!-- Highlight Box (replacing OTP box) -->
                        <div style="margin:25px 0;">
                            <span style="display:inline-block; padding:15px 25px; font-size:20px; background:#f1f7ff; color:#2d89ef; border-radius:8px; font-weight:bold;">
                                7-Day FREE Trial Activated
                            </span>
                        </div>

                        <p style="color:#666;">
                            You can use all features without any limitation during this trial period.
                        </p>

                        <p style="color:#666;">
                            After 7 days, you will need to upgrade to a <a href="https://bokli.io/subscription" style="color:#2d89ef; text-decoration:none;">Premium plan</a> to continue using our services.
                        </p>

                        <p style="color:#666;">
                            We recommend upgrading before your trial ends to avoid any interruption.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f9f9f9; padding:20px; text-align:center; font-size:12px; color:#999;">
                        If you have any questions, feel free to contact our support team.
                        <br><br>
                        © {{ date('Y') }} <a href="https://bokli.io" style="color:#2d89ef; text-decoration:none;">Bokli.io</a>. All rights reserved.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>
```

</body>
</html>
