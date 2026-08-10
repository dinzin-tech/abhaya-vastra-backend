@extends('emails.layout', ['heroBg' => '#1a1a1a', 'emailTitle' => 'Welcome to Abhaya Vastra'])

@section('content')

<!-- HEADER -->
<div class="email-header">
  <div class="brand-name">Abhaya Vastra</div>
</div>

<!-- HERO -->
<div class="email-hero" style="background:#1a1a1a;">
  <span class="status-icon">✨</span>
  <div class="email-title">Welcome to Abhaya Vastra</div>
  <div class="email-subtitle">We are thrilled to have you as part of our family</div>
</div>

<!-- BODY -->
<div class="email-body">

  <p class="greeting">
    Dear <strong>{{ $user->name }}</strong>,<br/><br/>
    {!! nl2br(e($bodyContent)) !!}
  </p>

  <hr class="divider"/>

  <!-- Features Grid -->
  <div class="section-title">What You Can Expect</div>
  <div class="info-grid">
    <div class="info-cell">
      <div class="info-label">💎 Premium Quality</div>
      <div class="info-value">Every piece is handcrafted with premium materials and meticulous attention to detail.</div>
    </div>
    <div class="info-cell">
      <div class="info-label">🚚 Fast Delivery</div>
      <div class="info-value">We ship across India within 3–7 business days with real-time tracking.</div>
    </div>
  </div>

  <div class="info-grid" style="margin-top:16px;">
    <div class="info-cell">
      <div class="info-label">💰 Reward Points</div>
      <div class="info-value">Earn loyalty points on every order and redeem them for discounts on future purchases.</div>
    </div>
    <div class="info-cell">
      <div class="info-label">🎧 Customer Support</div>
      <div class="info-value">Have a question? Reach us anytime at info@abhayavastra.store — we are always here to help.</div>
    </div>
  </div>

  <hr class="divider"/>

  <!-- CTA -->
  <div class="cta-wrapper">
    <a href="http://localhost:3000" class="cta-button">Explore Our Collection</a>
  </div>

  <div class="alert-box alert-success">
    <p>✨ <strong>Thank you for joining!</strong> We are crafting something special for you. Stay tuned for exclusive offers and new arrivals from Abhaya Vastra.</p>
  </div>

</div>

@endsection
