<!DOCTYPE html>
<html>
<head>
    <title>Booking Reminder</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
        <tr>
            <td align="center">

                <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05);">

                    <tr>
                        <td align="center" style="padding:20px;">
                            <img src="{{ $message->embed(public_path('logo.png')) }}" style="max-height:50px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; text-align:center;">

                            <h2 style="margin:0; color:#333;">Booking Reminder</h2>

                            <p style="color:#666; margin-top:10px;">
                                Hello <b>{{ $booking->user->name }}</b>,
                            </p>

                            @if($type == '24 hours')
                                <p style="color:#666;">Your booking is scheduled in <b>24 hours</b>.</p>
                            @else
                                <p style="color:#666;">Your booking starts in <b>1 hour</b>. Please arrive on time.</p>
                            @endif

                            <div style="margin:25px 0; text-align: left; background:#f1f7ff; padding:20px; border-radius:8px;">
                                <h4 style="margin:0 0 10px 0; color:#2d89ef;">Booking Details:</h4>
                                <table width="100%" style="color:#555; font-size:14px;">
                                    <tr>
                                        <td style="padding:5px 0;"><b>Date & Time:</b></td>
                                        <td style="padding:5px 0; text-align:right;">{{ $booking->date_time }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:5px 0;"><b>Status:</b></td>
                                        <td style="padding:5px 0; text-align:right;">
                                            <span style="background:#dcfce7; color:#166534; padding:2px 8px; border-radius:4px; font-size:12px;">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if($booking->service)
                                    <tr>
                                        <td style="padding:5px 0;"><b>Service:</b></td>
                                        <td style="padding:5px 0; text-align:right;">{{ $booking->service->name }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            <p style="color:#666; font-size: 14px;">
                                Thank you for choosing our service!
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f9f9f9; padding:20px; text-align:center; font-size:12px; color:#999;">
                            If you did not make this booking, please contact our support team.
                            <br><br>
                            © {{ date('Y') }} <a href="https://bokli.io" style="color:#2d89ef; text-decoration:none;">Bokli.io</a>. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
