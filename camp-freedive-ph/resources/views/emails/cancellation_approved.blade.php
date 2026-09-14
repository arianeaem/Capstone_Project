<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cancellation Request Approved - Camp FreedivePH</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1D1D1F; background-color: #F2F2F7; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #E5E5EA; }
        .header { background: #5E0000; color: #ffffff; padding: 28px 24px; text-align: center; }
        .content { padding: 32px 24px; }
        .status-box { background: #F8EAEA; border: 1px solid #FECACA; border-radius: 10px; padding: 16px; color: #991B1B; margin: 20px 0; }
        .refund-box { background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 10px; padding: 16px; color: #065F46; margin: 20px 0; }
        .btn { display: inline-block; background: #780000; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { background: #F2F2F7; padding: 20px; text-align: center; font-size: 14px; color: #8E8E93; border-top: 1px solid #E5E5EA; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Cancellation Approved</h2>
            <p style="margin: 4px 0 0 0; opacity: 0.9;">Booking #{{ $booking->booking_number }}</p>
        </div>
        <div class="content">
            <p>Hi <strong>{{ $booking->contact_name }}</strong>,</p>
            <p>Your request to cancel Booking <strong>#{{ $booking->booking_number }}</strong> has been processed by Camp FreedivePH administration.</p>

            @if(!$isForfeited && $refundAmount > 0)
                <div class="refund-box">
                    <strong>Refund Approved & Queued for Processing</strong><br>
                    <span>Approved Refund Amount: <strong>₱{{ number_format($refundAmount, 2) }}</strong></span><br>
                    <span style="font-size: 14px;">Our finance staff has queued your refund back to your original payment method (PayMongo / GCash / Maya).</span>
                </div>
            @else
                <div class="status-box">
                    <strong>Cancellation Approved (Downpayment Forfeited)</strong><br>
                    <span>Per camp policy, cancellations made within 14 days of departure result in downpayment forfeiture.</span>
                </div>
            @endif

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Cancelled Dates:</td>
                    <td style="padding: 10px 0; font-weight: 600;">{{ $booking->start_date->format('M d, Y') }} to {{ $booking->end_date->format('M d, Y') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Booking Reference:</td>
                    <td style="padding: 10px 0; font-weight: 700; color: #780000;">{{ $booking->booking_number }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #E5E5EA;">
                    <td style="padding: 10px 0; color: #6E6E73;">Total Paid:</td>
                    <td style="padding: 10px 0; font-weight: 600;">₱{{ number_format($booking->paid_amount, 2) }}</td>
                </tr>
            </table>

            <p style="font-size: 14px; color: #6E6E73;">We hope to welcome you to the water on another weekend. You can review your account history anytime via Manage Booking.</p>
            
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/manage-booking/' . $booking->booking_number) }}" class="btn">View Cancellation Summary</a>
            </div>
        </div>
        <div class="footer">
            <p><strong>Camp FreedivePH</strong><br>The Shack Hideaway by Mayumi Resorts, Sitio Bagalangit Road, Barangay Bagalangit, Anilao, Mabini, Batangas, Philippines</p>
            <p style="margin-top: 8px;">
                <a href="https://www.facebook.com/Campfreediveph/" target="_blank" rel="noopener noreferrer" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">Facebook</a> &bull;
                <a href="https://www.instagram.com/campfreediveph/" target="_blank" rel="noopener noreferrer" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">Instagram</a> &bull;
                <a href="mailto:campfreediveph@gmail.com" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">campfreediveph@gmail.com</a> &bull;
                <a href="tel:+639278879894" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">+63 927 887 9894</a>
            </p>
        </div>
    </div>
</body>
</html>
