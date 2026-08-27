<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reschedule Request - Camp FreedivePH</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1D1D1F; background-color: #F2F2F7; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #E5E5EA; }
        .header { background: #5E0000; color: #ffffff; padding: 28px 24px; text-align: center; }
        .content { padding: 32px 24px; }
        .status-box { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 16px; color: #92400E; margin: 20px 0; }
        .btn { display: inline-block; background: #780000; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { background: #FAFAFC; padding: 20px; text-align: center; font-size: 12px; color: #8E8E93; border-top: 1px solid #E5E5EA; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Reschedule Request Received</h2>
            <p style="margin: 4px 0 0 0; opacity: 0.9;">Booking #{{ $booking->booking_number }}</p>
        </div>
        <div class="content">
            <p>Hi <strong>{{ $booking->contact_name }}</strong>,</p>
            <p>We received your request to reschedule your dive trip with Camp FreedivePH.</p>

            <div class="status-box">
                <strong>Status: Pending Camp Approval</strong><br>
                Your request has been logged. Our camp coordinator is reviewing coach and bed allocations for your new requested dates.
            </div>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Original Dates:</td>
                    <td style="padding: 10px 0; font-weight: 600;">{{ $rescheduleRequest->current_start_date->format('M d, Y') }} - {{ $rescheduleRequest->current_end_date->format('M d, Y') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Requested New Dates:</td>
                    <td style="padding: 10px 0; font-weight: 700; color: #780000;">{{ $rescheduleRequest->requested_start_date->format('M d, Y') }} - {{ $rescheduleRequest->requested_end_date->format('M d, Y') }}</td>
                </tr>
                @if($rescheduleRequest->reason)
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Reason provided:</td>
                    <td style="padding: 10px 0;">{{ $rescheduleRequest->reason }}</td>
                </tr>
                @endif
            </table>

            <p style="font-size: 13px; color: #6E6E73;">You can track the status of your booking anytime using your Booking Number and PIN on the portal.</p>
            
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/manage-booking') }}" class="btn">View Booking Status</a>
            </div>
        </div>
        <div class="footer">
            Camp FreedivePH<br>Mabini, Batangas
        </div>
    </div>
</body>
</html>
