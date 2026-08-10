@extends('emails.layout', ['heroBg' => '#1a1a1a', 'emailTitle' => 'Order Status Update - Abhaya Vastra'])

@section('content')

<!-- HEADER -->
<div class="email-header">
  <div class="brand-name">Abhaya Vastra</div>
  <div class="brand-tagline">Premium Jewellery</div>
</div>

<!-- HERO -->
<div class="email-hero" style="background:#1a1a1a;">
  <span class="status-icon">📦</span>
  <div class="email-title">Order Status Updated</div>
  <div class="email-subtitle">There's a new update on your order</div>
</div>

<!-- BODY -->
<div class="email-body">

  <p class="greeting">
    Dear <strong>{{ $order->name }}</strong>,<br/><br/>
    {!! nl2br(e($bodyContent)) !!}
  </p>

  <!-- Order Number -->
  <div class="order-badge">
    <div class="label">Order Number</div>
    <div class="value">#{{ $order->order_number }}</div>
  </div>

  <div class="section-title">Current Status</div>
  <p style="margin-bottom:24px;">
    <span class="status-badge status-{{ strtolower($order->status) }}">{{ ucfirst($order->status) }}</span>
  </p>

  <!-- Items -->
  <div class="section-title">Your Items</div>
  <table class="items-table">
    @foreach($order->items as $item)
    <tr class="item-row">
      <td class="item-name">
        {{ $item['name'] }}
        <div class="item-meta">Qty: {{ $item['quantity'] }}</div>
      </td>
      <td class="item-price">₹{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
    </tr>
    @endforeach
  </table>

  <!-- CTA -->
  <div class="cta-wrapper">
    <a href="http://localhost:3000/orders" class="cta-button">View Order Details</a>
  </div>

</div>

@endsection
