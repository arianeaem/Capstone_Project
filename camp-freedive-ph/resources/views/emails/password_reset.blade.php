<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Your Password - Camp FreedivePH</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; line-height: 1.6; color: #1D1D1F; background-color: #F2F2F7; margin: 0; padding: 20px; font-size: 14px; }
        .container { max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #E5E5EA; }
        .header { background: #780000; color: #ffffff; padding: 30px 24px; text-align: center; }
        .header h1 { margin: 0 0 6px 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 0; opacity: 0.9; font-size: 14px; }
        .content { padding: 32px 24px; }
        .reset-box { background: #F8EAEA; border: 1px solid #F1D5D5; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0; }
        .btn { display: inline-block; background: #780000; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 14px; box-shadow: 0 2px 6px rgba(120,0,0,0.25); }
        .footer { background: #F2F2F7; padding: 20px; text-align: center; font-size: 14px; color: #8E8E93; border-top: 1px solid #E5E5EA; }
        .url-text { word-break: break-all; color: #780000; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
            <p>Camp FreedivePH</p>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            <p>We received a request to reset the password for your Camp FreedivePH staff account (<strong>{{ $user->email }}</strong>).</p>
            
            <div class="reset-box">
                <p style="margin: 0 0 16px 0; font-size: 14px; color: #470000; font-weight: 600;">Click the button below to choose a new password:</p>
                <a href="{{ $resetUrl }}" class="btn" target="_blank">Reset My Password</a>
                <p style="font-size: 14px; color: #6E6E73; margin: 16px 0 0 0;">This password reset link will expire in 60 minutes.</p>
            </div>

            <p style="font-size: 14px; color: #6E6E73;">If you did not request a password reset, no further action is required and your account remains safe.</p>

            <hr style="border: none; border-top: 1px solid #E5E5EA; margin: 24px 0;">

            <p style="font-size: 14px; color: #8E8E93;">If you're having trouble clicking the "Reset My Password" button, copy and paste the URL below into your web browser:</p>
            <p class="url-text">{{ $resetUrl }}</p>
        </div>
        <div class="footer">
            <p style="margin: 0 0 6px 0;"><strong>Camp FreedivePH</strong> &bull; Anilao, Mabini, Batangas, Philippines</p>
            <p style="margin: 0;">
                <a href="https://www.facebook.com/Campfreediveph/" target="_blank" rel="noopener noreferrer" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">Facebook</a> &bull;
                <a href="https://www.instagram.com/campfreediveph/" target="_blank" rel="noopener noreferrer" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">Instagram</a> &bull;
                <a href="mailto:campfreediveph@gmail.com" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">campfreediveph@gmail.com</a> &bull;
                <a href="tel:+639278879894" style="color: #780000; font-weight: 600; text-decoration: underline; margin: 0 4px;">+63 927 887 9894</a>
            </p>
        </div>
    </div>
</body>
</html>
