@extends('emails.layout', ['heroBg' => '#0d3320', 'emailTitle' => 'Order Delivered - Abhaya Vastra'])

@section('content')

<!-- HEADER -->
<div class="email-header">
  <div class="brand-name">Abhaya Vastra</div>
</div>

<!-- HERO -->
<div class="email-hero" style="background:#0d3320;">
  <span class="status-icon">🎉</span>
  <div class="email-title">Your Order Has Arrived!</div>
  <div class="email-subtitle">We hope you love your new jewellery</div>
</div>

<!-- BODY -->
<div class="email-body">

  <p class="greeting">
    Dear <strong>{{ $order->name }}</strong>,<br/><br/>
    Your order has been successfully delivered! We hope everything arrived in perfect condition and that you love your new piece from Abhaya Vastra.
  </p>

  <!-- Order Number -->
  <div class="order-badge">
    <div class="label">Order Number</div>
    <div class="value">#{{ $order->order_number }}</div>
  </div>

  <!-- Items Delivered -->
  <div class="section-title">Items Delivered</div>
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

  <!-- Order Summary -->
  <table class="totals-table">
    <tr>
      <td class="label-col">Order Total</td>
      <td class="value-col">₹{{ number_format($order->total, 2) }}</td>
    </tr>
    <tr>
      <td class="label-col">Payment Method</td>
      <td class="value-col">{{ strtoupper($order->payment_method) }}</td>
    </tr>
  </table>

  <hr class="divider"/>

  <div class="alert-box alert-success">
    <p>✨ <strong>Loved your order?</strong> Share your experience and leave a review on your order page — it helps us grow and serve you better!</p>
  </div>

  <!-- CTA -->
  <div class="cta-wrapper">
    <a href="http://localhost:3000/orders" class="cta-button">Write a Review</a>
  </div>

  <div class="alert-box alert-warning">
    <p>🔄 <strong>Need an exchange or return?</strong> You can raise a request within 7 days of delivery from your order history page. We're happy to help!</p>
  </div>

</div>

@endsection
