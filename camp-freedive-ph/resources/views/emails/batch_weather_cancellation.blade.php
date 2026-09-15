<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Camp Cancellation & Refund Notice - Camp FreedivePH</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1D1D1F; background-color: #F2F2F7; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #E5E5EA; }
        .header { background: #5E0000; color: #ffffff; padding: 28px 24px; text-align: center; }
        .content { padding: 32px 24px; }
        .alert-box { background: #FEF2F2; border: 1px solid #FECACA; border-radius: 12px; padding: 18px; color: #991B1B; margin: 20px 0; }
        .refund-box { background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 12px; padding: 18px; color: #065F46; margin: 20px 0; }
        .pin-badge { display: inline-block; background: #F2F2F7; border: 1px solid #D1D1D6; border-radius: 6px; padding: 4px 10px; font-family: monospace; font-size: 16px; font-weight: 700; color: #1D1D1F; letter-spacing: 2px; }
        .btn { display: inline-block; background: #780000; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 14px; text-align: center; }
        .btn-outline { display: inline-block; background: #ffffff; color: #780000 !important; border: 1.5px solid #780000; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 14px; margin-left: 8px; text-align: center; }
        .footer { background: #F2F2F7; padding: 20px; text-align: center; font-size: 13px; color: #8E8E93; border-top: 1px solid #E5E5EA; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0; font-size: 22px; font-weight: 800;">Camp Cancellation Notice</h2>
            <p style="margin: 6px 0 0 0; opacity: 0.9; font-size: 14px;">Marine Safety & Severe Weather Action</p>
        </div>
        <div class="content">
            <p>Good day, <strong>{{ $booking->contact_name }}</strong>,</p>
            
            <p>We regret to inform you that your upcoming 2D1N freediving camp scheduled for <strong>{{ $booking->start_date ? $booking->start_date->format('M d, Y') : '' }} to {{ $booking->end_date ? $booking->end_date->format('M d, Y') : '' }}</strong> has been officially cancelled by camp administration due to severe weather/marine safety advisories:</p>

            <div class="alert-box">
                <strong style="display: block; margin-bottom: 4px; font-size: 15px;">Safety Cancellation Reason:</strong>
                <span style="font-size: 14px; line-height: 1.5;">{{ $cancellationReason }}</span>
            </div>

            <div class="refund-box">
                <strong style="display: block; margin-bottom: 4px; font-size: 15px; color: #065F46;">100% Force Majeure Protection & Options:</strong>
                <span style="font-size: 14px; line-height: 1.5;">Because safety is our highest priority, your booking qualifies for full protection. You can choose either:</span>
                <ul style="margin: 8px 0 0 0; padding-left: 20px; font-size: 14px;">
                    <li><strong>Full Refund (100%)</strong>: Automatically approved and queued for return to your original payment method.</li>
                    <li><strong>Free Reschedule</strong>: Transfer your payment to any upcoming open weekend batch without penalty.</li>
                </ul>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin: 24px 0; font-size: 14px;">
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Booking Number:</td>
                    <td style="padding: 10px 0; font-weight: 700; color: #780000; font-family: monospace; font-size: 15px;">{{ $booking->booking_number }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Guest Security PIN:</td>
                    <td style="padding: 10px 0;"><span class="pin-badge">{{ $booking->pin }}</span></td>
                </tr>
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Class Package:</td>
                    <td style="padding: 10px 0; font-weight: 600; text-transform: capitalize;">{{ $booking->class_type }} Class ({{ $booking->participants_count ?? ($booking->participants ? $booking->participants->count() : 1) }} Diver/s)</td>
                </tr>
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Total Downpayment Paid:</td>
                    <td style="padding: 10px 0; font-weight: 700; color: #166534;">₱{{ number_format($booking->downpayment_amount ?? 0, 2) }}</td>
                </tr>
            </table>

            <p style="font-size: 14px; color: #6E6E73; margin-bottom: 24px;">
                You can select your preferred option (Refund or Reschedule) directly through the self-service booking portal using your Booking Number and PIN:
            </p>
            
            <div style="text-align: center; margin-top: 10px; margin-bottom: 24px;">
                <a href="{{ route('manage.show', ['booking_number' => $booking->booking_number, 'pin' => $booking->pin]) }}" class="btn">Select Refund or Reschedule</a>
            </div>

            <p style="font-size: 13px; color: #8E8E93; text-align: center;">
                If you have any questions, our support team is available at <a href="mailto:gustoariane@gmail.com" style="color: #780000; font-weight: 600;">gustoariane@gmail.com</a>.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0 0 6px 0;"><strong>Camp FreedivePH</strong><br>The Shack Hideaway by Mayumi Resorts, Sitio Bagalangit Road, Anilao, Mabini, Batangas, Philippines</p>
            <p style="margin: 0;">
                <a href="mailto:gustoariane@gmail.com" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">gustoariane@gmail.com</a> &bull;
                <a href="tel:+639278879894" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">+63 927 887 9894</a>
            </p>
        </div>
    </div>
</body>
</html>
