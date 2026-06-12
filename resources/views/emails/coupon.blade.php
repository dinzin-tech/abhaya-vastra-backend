<!-- resources/views/emails/coupon.blade.php -->
<!DOCTYPE html>
<html lang="en" style="margin:0; padding:0;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Coupon</title>
</head>
<body style="font-family: 'Arial', sans-serif; margin:0; padding:0; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#10b981; padding:20px; text-align:center;color:#fff;">
                            <h1 style="margin:0; font-size:24px; color:#fff;" class="text-white">🎁 Special Coupon Just for You!</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333;">
                            @if($messageBody)
                                <p style="font-size:16px; line-height:1.5;">{{ $messageBody }}</p>
                            @else
                                <p style="font-size:16px; line-height:1.5;">We are excited to give you an exclusive coupon to enjoy discounts on your next purchase!</p>
                            @endif

                            <table cellpadding="0" cellspacing="0" style="margin:20px 0; width:100%; border:1px dashed #ccc; border-radius:8px; text-align:center;">
                                <tr>
                                    <td style="padding:20px;">
                                        <h2 style="margin:0; font-size:20px; color:#8B4513;">{{ $coupon->code }}</h2>
                                        <p style="margin:5px 0 0 0; font-size:16px;">Value: <strong>{{ $coupon->value }}{{ $coupon->type == 'percentage' ? '%' : '₹' }}</strong></p>
                                        @if($coupon->expires_at)
                                            <p style="margin:5px 0 0 0; font-size:14px; color:#555;">Expires: {{ \Carbon\Carbon::parse($coupon->expires_at)->format('d M Y') }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align:center; margin:30px 0 0 0;">
                                <a href="{{ url('/') }}" style="background-color:#8B4513; color:#fff; text-decoration:none; padding:12px 25px; border-radius:5px; font-weight:bold;">Use Coupon Now</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f4f4f4; padding:15px; text-align:center; font-size:12px; color:#888;">
                            <p style="margin:0;">You are receiving this email because you subscribed to our newsletter.</p>
                            <p style="margin:0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
