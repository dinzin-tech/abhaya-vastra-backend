@extends('emails.layout', [
    'emailTitle' => 'Special Coupon - Abhaya Vastra',
    'heroBg' => '#1a1a1a'
])

@section('content')

<!-- HERO -->
<div class="email-hero">
  <span class="status-icon">🎁</span>
  <h1 class="email-title">Special Coupon Just for You</h1>
  <p class="email-subtitle">Exclusive Savings on Your Next Order</p>
</div>

<!-- BODY -->
<div class="email-body">
  <p class="greeting">
    @if($messageBody)
      {!! nl2br(e($messageBody)) !!}
    @else
      We are delighted to present you with an exclusive discount coupon to enjoy on your next purchase at <strong>Abhaya Vastra</strong>.
    @endif
  </p>

  <!-- COUPON BADGE -->
  <div style="background: #fdf6ec; border: 1.5px dashed #c9a96e; border-radius: 8px; padding: 24px 20px; margin: 28px 0; text-align: center;">
    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.25em; text-transform: uppercase; color: #888; margin-bottom: 6px;">
      YOUR EXCLUSIVE PROMO CODE
    </div>
    <div style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 32px; font-weight: 700; color: #1a1a1a; letter-spacing: 0.15em; margin: 6px 0;">
      {{ $coupon->code }}
    </div>
    <div style="font-size: 15px; font-weight: 600; color: #c9a96e; margin-top: 4px;">
      SAVINGS: {{ $coupon->type == 'percentage' ? intval($coupon->value) . '% OFF' : '₹' . intval($coupon->value) . ' OFF' }}
      @if($coupon->min_cart_amount > 0)
        <span style="font-size: 12px; color: #666; font-weight: 400; display: block; margin-top: 4px;">
          (Valid on orders above ₹{{ number_format($coupon->min_cart_amount, 2) }})
        </span>
      @endif
    </div>
    @if($coupon->expires_at)
      <div style="font-size: 12px; color: #888; margin-top: 10px; font-weight: 400;">
        ⏰ Expires on {{ \Carbon\Carbon::parse($coupon->expires_at)->format('M d, Y') }}
      </div>
    @endif
  </div>

  <!-- CTA BUTTON -->
  <div class="cta-wrapper" style="text-align: center; margin: 32px 0 20px;">
    <a href="{{ config('app.frontend_url') }}/checkout?coupon={{ urlencode($coupon->code) }}" class="cta-button" style="display: inline-block; background: #1a1a1a; color: #c9a96e !important; text-decoration: none; padding: 14px 36px; font-size: 11px; font-weight: 600; letter-spacing: 0.25em; text-transform: uppercase; border: 1px solid #1a1a1a;">
      USE COUPON NOW &rarr;
    </a>
  </div>

  <p style="font-size: 12px; color: #777; text-align: center; margin-top: 16px;">
    Simply click the button above or enter <strong>{{ $coupon->code }}</strong> at checkout to redeem your savings.
  </p>
</div>

@endsection
