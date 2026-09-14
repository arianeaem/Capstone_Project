<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Confirmed - Camp FreedivePH</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1D1D1F; background-color: #F2F2F7; margin: 0; padding: 20px; font-size: 14px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #E5E5EA; }
        .header { background: #780000; color: #ffffff; padding: 32px 24px; text-align: center; }
        .header h1 { margin: 0 0 8px 0; font-size: 24px; font-weight: 700; }
        .header p { margin: 0; opacity: 0.9; font-size: 14px; }
        .content { padding: 32px 24px; }
        .pin-box { background: #F8EAEA; border: 2px dashed #780000; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0; }
        .pin-box .label { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #780000; font-weight: 700; }
        .pin-box .number { font-size: 28px; font-weight: 800; color: #780000; margin: 4px 0; font-family: monospace; letter-spacing: 2px; }
        .pin-box .pin { font-size: 18px; font-weight: 600; color: #470000; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #E5E5EA; font-size: 14px; }
        .table th { color: #6E6E73; font-weight: 600; }
        .total-row { font-weight: 700; color: #1D1D1F; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 14px; font-weight: 600; background: #ECFDF5; color: #065F46; }
        .footer { background: #F2F2F7; padding: 24px; text-align: center; font-size: 14px; color: #8E8E93; border-top: 1px solid #E5E5EA; }
        .btn { display: inline-block; background: #780000; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Confirmed</h1>
            <p>Camp FreedivePH | Mabini, Batangas</p>
        </div>
        <div class="content">
            <p>Hi <strong>{{ $booking->contact_name }}</strong>,</p>
            <p>Thank you for booking with Camp FreedivePH. Your downpayment has been received and your slot is officially secured.</p>

            <div class="pin-box">
                <div class="label">Your Self-Service Access Credentials</div>
                <div class="number">{{ $booking->booking_number }}</div>
                <div class="pin">PIN: <strong>{{ $booking->pin }}</strong></div>
                <p style="font-size: 14px; color: #6E6E73; margin: 8px 0 0 0;">Save this email or screenshot these credentials to manage your booking anytime.</p>
            </div>

            <table class="table">
                <tr>
                    <th>Class Package</th>
                    <td><strong>{{ $booking->formatted_class_type }}</strong></td>
                </tr>
                <tr>
                    <th>Trip Dates</th>
                    <td>{{ $booking->start_date->format('F d, Y (l)') }} - {{ $booking->end_date->format('F d, Y (l)') }}</td>
                </tr>
                <tr>
                    <th>Participants ({{ $booking->participants->count() }})</th>
                    <td>
                        @foreach($booking->participants as $participant)
                            <div>{{ $participant->name }} (Age {{ $participant->age }})</div>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>Transportation</th>
                    <td>
                        @if($booking->pickup_option === 'carpool')
                            Carpool: <strong>{{ $booking->pickup_location }}</strong>
                        @else
                            Own Transportation
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Boat Dive</th>
                    <td>{{ $booking->boat_dive ? 'Yes (Included)' : 'No' }}</td>
                </tr>
                @if($booking->priceAdjustments && $booking->priceAdjustments->count() > 0)
                <tr>
                    <th>Seasonal / Demand Adjustments</th>
                    <td>
                        @foreach($booking->priceAdjustments as $adj)
                            <div style="font-size: 14px; margin-bottom: 3px;">
                                <strong>{{ $adj->rule_name }}:</strong> 
                                <span style="color: {{ $adj->adjustment_amount >= 0 ? '#B91C1C' : '#047857' }};">
                                    {{ $adj->adjustment_amount >= 0 ? '+' : '−' }}₱{{ number_format(abs($adj->adjustment_amount) * $booking->participants->count(), 2) }}
                                </span>
                            </div>
                        @endforeach
                    </td>
                </tr>
                @endif
                <tr class="total-row">
                    <th>Total Trip Cost</th>
                    <td>₱{{ number_format($booking->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Downpayment Paid</th>
                    <td><span class="badge">₱{{ number_format($booking->downpayment_amount, 2) }} Paid</span></td>
                </tr>
                <tr style="color: #780000; font-weight: 700;">
                    <th>Balance Due at Camp Check-in</th>
                    <td>₱{{ number_format($booking->balance_amount, 2) }}</td>
                </tr>
            </table>

            <div style="background: #F2F2F7; border-radius: 12px; padding: 18px; margin-top: 24px; font-size: 14px; border: 1px solid #E5E5EA;">
                <h4 style="margin: 0 0 10px 0; color: #1D1D1F; font-size: 15px;">Things to Bring:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #4A4A4F; line-height: 1.8;">
                    <li>Swimming clothes (anything you’re comfortable wearing)</li>
                    <li>Toiletries</li>
                    <li>Personal things</li>
                    <li>A pair of socks (in any kind) for fin fitting</li>
                </ul>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #065F46; font-weight: 600;">
                    (Towels, shampoo and soap are all provided)
                </p>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/manage-booking') }}" class="btn">Manage Your Booking Online</a>
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
