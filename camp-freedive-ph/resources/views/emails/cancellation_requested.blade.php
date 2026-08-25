<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cancellation Request - Camp FreedivePH</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1D1D1F; background-color: #F2F2F7; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #E5E5EA; }
        .header { background: #470000; color: #ffffff; padding: 28px 24px; text-align: center; }
        .content { padding: 32px 24px; }
        .status-box { background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 16px; color: #991B1B; margin: 20px 0; }
        .btn { display: inline-block; background: #780000; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { background: #FAFAFC; padding: 20px; text-align: center; font-size: 12px; color: #8E8E93; border-top: 1px solid #E5E5EA; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Cancellation Request Received</h2>
            <p style="margin: 4px 0 0 0; opacity: 0.9;">Booking #{{ $booking->booking_number }}</p>
        </div>
        <div class="content">
            <p>Hi <strong>{{ $booking->contact_name }}</strong>,</p>
            <p>We received your request to cancel your booking with Camp FreedivePH.</p>

            <div class="status-box">
                <strong>Status: Cancellation Pending Camp Review</strong><br>
                Calculated Policy Refund: <strong>₱{{ number_format($cancellationRequest->calculated_refund_amount, 2) }}</strong>
                @if($cancellationRequest->force_majeure_flag)
                    <br><span style="font-size: 12px; color: #991B1B;">(Force Majeure / Marine Safety Advisory applied)</span>
                @endif
            </div>

            <p style="font-size: 14px; color: #1D1D1F;">
                Our finance and booking team is reviewing your request according to the camp cancellation policy. Approved refunds are processed back via GCash or original payment channel within 3-5 banking days.
            </p>

            @if($cancellationRequest->reason)
            <div style="background: #FAFAFC; padding: 12px; border-radius: 8px; font-size: 13px; color: #6E6E73; margin: 16px 0;">
                <strong>Reason:</strong> {{ $cancellationRequest->reason }}
            </div>
            @endif

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/manage-booking') }}" class="btn">View Booking Details</a>
            </div>
        </div>
        <div class="footer">
            Camp FreedivePH<br>Mabini, Batangas
        </div>
    </div>
</body>
</html>
