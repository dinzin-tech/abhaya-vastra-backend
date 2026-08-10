@extends('emails.layout', ['heroBg' => '#1a1a1a', 'emailTitle' => 'Order Confirmed - Abhaya Vastra'])

@section('content')

<!-- HEADER -->
<div class="email-header">
  <div class="brand-name">Abhaya Vastra</div>
</div>

<!-- HERO -->
<div class="email-hero" style="background:#1a1a1a;">
  <span class="status-icon">💎</span>
  <div class="email-title">Order Confirmed!</div>
  <div class="email-subtitle">Thank you for your purchase — we are preparing your order</div>
</div>

<!-- BODY -->
<div class="email-body">

  <p class="greeting">
    Dear <strong>{{ $order->name }}</strong>,<br/><br/>
    We have received your order and it is being processed. You will receive another email once it ships.
  </p>

  <!-- Order Number -->
  <div class="order-badge">
    <div class="label">Order Number</div>
    <div class="value">#{{ $order->order_number }}</div>
  </div>

  <!-- Items -->
  <div class="section-title">Items Ordered</div>
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

  <!-- Totals -->
  <table class="totals-table">
    <tr>
      <td class="label-col">Subtotal</td>
      <td class="value-col">₹{{ number_format($order->subtotal, 2) }}</td>
    </tr>
    @if($order->discount > 0)
    <tr class="discount-row">
      <td class="label-col">Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</td>
      <td class="value-col">− ₹{{ number_format($order->discount, 2) }}</td>
    </tr>
    @endif
    @if($order->wallet_money_used > 0)
    <tr class="discount-row">
      <td class="label-col">Wallet Used</td>
      <td class="value-col">− ₹{{ number_format($order->wallet_money_used, 2) }}</td>
    </tr>
    @endif
    <tr>
      <td class="label-col">Shipping</td>
      <td class="value-col">₹{{ number_format($order->shipping_charge, 2) }}</td>
    </tr>
    <tr class="total-row">
      <td class="label-col">Total Paid</td>
      <td class="value-col">₹{{ number_format($order->total, 2) }}</td>
    </tr>
  </table>

  <hr class="divider"/>

  <!-- Address + Payment Info -->
  <div class="section-title">Delivery & Payment Details</div>
  <div class="info-grid">
    <div class="info-cell">
      <div class="info-label">Shipping To</div>
      <div class="info-value">
        {{ $order->name }}<br/>
        {{ $order->address }}<br/>
        {{ $order->city }}, {{ $order->state }} — {{ $order->zip }}<br/>
        📞 {{ $order->phone }}
      </div>
    </div>
    <div class="info-cell">
      <div class="info-label">Payment</div>
      <div class="info-value">
        Method: {{ strtoupper($order->payment_method) }}<br/>
        Status: <span class="status-badge status-{{ $order->payment_status == 'completed' ? 'delivered' : 'processing' }}">{{ ucfirst($order->payment_status) }}</span>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="cta-wrapper">
    <a href="http://localhost:3000/orders" class="cta-button">Track My Order</a>
  </div>

  <div class="alert-box alert-success">
    <p>✨ <strong>Thank you for choosing Abhaya Vastra!</strong> We craft every piece with love and care. If you have any questions, reply to this email — we are here to help.</p>
  </div>

</div>

@endsection
