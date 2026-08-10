@extends('emails.layout', ['heroBg' => '#0c1f3f', 'emailTitle' => 'Order Shipped - Abhaya Vastra'])

@section('content')

<!-- HEADER -->
<div class="email-header">
  <div class="brand-name">Abhaya Vastra</div>
  <div class="brand-tagline">Premium Jewellery</div>
</div>

<!-- HERO -->
<div class="email-hero" style="background:#0c1f3f;">
  <span class="status-icon">🚀</span>
  <div class="email-title">Your Order is On Its Way!</div>
  <div class="email-subtitle">Sit back — your jewellery is headed to you</div>
</div>

<!-- BODY -->
<div class="email-body">

  <p class="greeting">
    Dear <strong>{{ $order->name }}</strong>,<br/><br/>
    Great news! Your order has been shipped and is on its way to you. You can track your package using the details below.
  </p>

  <!-- Order Number -->
  <div class="order-badge">
    <div class="label">Order Number</div>
    <div class="value">#{{ $order->order_number }}</div>
  </div>

  <!-- Tracking Info -->
  @if(!empty($order->shiprocket_awb_code) || !empty($order->qikink_awb_code))
  <div class="section-title">Tracking Information</div>
  <div class="tracking-box">
    <div class="tracking-label">AWB / Tracking Number</div>
    <div class="awb">{{ $order->shiprocket_awb_code ?? $order->qikink_awb_code }}</div>
    @if(!empty($order->shiprocket_tracking_url))
    <a href="{{ $order->shiprocket_tracking_url }}" class="track-link">→ Track My Package</a>
    @elseif(!empty($order->qikink_tracking_url))
    <a href="{{ $order->qikink_tracking_url }}" class="track-link">→ Track My Package</a>
    @endif
  </div>
  @endif

  <!-- Items -->
  <div class="section-title">Items in This Shipment</div>
  <table class="items-table">
    @foreach($order->items as $item)
    <tr class="item-row">
      <td class="item-name">
        {{ $item['name'] }}
        <div class="item-meta">
          Qty: {{ $item['quantity'] }}
          @if(!empty($item['size']) && $item['size'] !== 'One Size') &nbsp;•&nbsp; Size: {{ $item['size'] }} @endif
          @if(!empty($item['color'])) &nbsp;•&nbsp; Color: {{ $item['color'] }} @endif
        </div>
      </td>
      <td class="item-price">₹{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
    </tr>
    @endforeach
  </table>

  <hr class="divider"/>

  <!-- Delivery Address -->
  <div class="section-title">Delivering To</div>
  <div class="info-grid">
    <div class="info-cell">
      <div class="info-label">Address</div>
      <div class="info-value">
        {{ $order->name }}<br/>
        {{ $order->address }}<br/>
        {{ $order->city }}, {{ $order->state }} — {{ $order->zip }}<br/>
        📞 {{ $order->phone }}
      </div>
    </div>
    <div class="info-cell">
      <div class="info-label">Order Total</div>
      <div class="info-value">
        <strong style="font-size:18px;color:#1a1a1a;">₹{{ number_format($order->total, 2) }}</strong><br/>
        Via {{ strtoupper($order->payment_method) }}
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="cta-wrapper">
    <a href="http://localhost:3000/orders" class="cta-button">View My Order</a>
  </div>

  <div class="alert-box alert-success">
    <p>📦 Your package is on its way! Expected delivery in <strong>3–7 business days</strong>. If you have any concerns, just reply to this email.</p>
  </div>

</div>

@endsection
