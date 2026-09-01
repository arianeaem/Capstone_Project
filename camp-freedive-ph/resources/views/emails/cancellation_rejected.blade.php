<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cancellation Request Update - Camp FreedivePH</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1D1D1F; background-color: #F2F2F7; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #E5E5EA; }
        .header { background: #470000; color: #ffffff; padding: 28px 24px; text-align: center; }
        .content { padding: 32px 24px; }
        .status-box { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 16px; color: #92400E; margin: 20px 0; }
        .btn { display: inline-block; background: #780000; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { background: #FAFAFC; padding: 20px; text-align: center; font-size: 12px; color: #8E8E93; border-top: 1px solid #E5E5EA; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Cancellation Request Update</h2>
            <p style="margin: 4px 0 0 0; opacity: 0.9;">Booking #{{ $booking->booking_number }}</p>
        </div>
        <div class="content">
            <p>Hi <strong>{{ $booking->contact_name }}</strong>,</p>
            <p>We are writing to update you regarding your cancellation request for Booking <strong>#{{ $booking->booking_number }}</strong>.</p>

            <div class="status-box">
                <strong>Cancellation Request Not Approved</strong><br>
                <span>{{ $reason }}</span>
            </div>

            <p>Your booking remains confirmed for your scheduled dive dates:</p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Confirmed Dates:</td>
                    <td style="padding: 10px 0; font-weight: 700; color: #780000;">{{ $booking->start_date->format('M d, Y') }} – {{ $booking->end_date->format('M d, Y') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Booking Reference:</td>
                    <td style="padding: 10px 0; font-weight: 700;">{{ $booking->booking_number }}</td>
                </tr>
            </table>

            <p style="font-size: 13px; color: #6E6E73;">If you have any questions or need further assistance, please reach out to camp coordinators or check Manage Booking.</p>
            
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/manage-booking/' . $booking->booking_number) }}" class="btn">View Booking Details</a>
            </div>
        </div>
        <div class="footer">
            Camp FreedivePH<br>Mabini, Batangas Base Camp
        </div>
    </div>
</body>
</html>
