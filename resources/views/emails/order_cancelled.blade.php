@extends('emails.layout', ['heroBg' => '#2d0a0a', 'emailTitle' => 'Order Cancelled - Abhaya Vastra'])

@section('content')

<!-- HEADER -->
<div class="email-header">
  <div class="brand-name">Abhaya Vastra</div>
  <div class="brand-tagline">Premium Jewellery</div>
</div>

<!-- HERO -->
<div class="email-hero" style="background:#2d0a0a;">
  <span class="status-icon">⚠️</span>
  <div class="email-title">Order Cancelled</div>
  <div class="email-subtitle">Your order #{{ $order->order_number }} has been cancelled</div>
</div>

<!-- BODY -->
<div class="email-body">

  <p class="greeting">
    Dear <strong>{{ $order->name }}</strong>,<br/><br/>
    We are sorry to inform you that your order has been cancelled. Please find the details below.
  </p>

  <!-- Order Number -->
  <div class="order-badge">
    <div class="label">Cancelled Order</div>
    <div class="value">#{{ $order->order_number }}</div>
  </div>

  @if(!empty($cancelReason))
  <!-- Cancellation Reason -->
  <div class="section-title">Reason for Cancellation</div>
  <div class="alert-box alert-danger" style="margin-bottom:28px;">
    <p>{{ $cancelReason }}</p>
  </div>
  @endif

  <!-- Items that were ordered -->
  <div class="section-title">Cancelled Items</div>
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

  <hr class="divider"/>

  <!-- Refund Details -->
  @if($order->wallet_money_used > 0 || $order->loyalty_points_used > 0)
  <div class="section-title">Refund Details</div>
  <div class="alert-box alert-success" style="margin-bottom:28px;">
    <p>
      @if($order->wallet_money_used > 0)
        💰 <strong>₹{{ number_format($order->wallet_money_used, 2) }}</strong> has been refunded to your wallet balance.<br/>
      @endif
      @if($order->loyalty_points_used > 0)
        ⭐ <strong>{{ $order->loyalty_points_used }} loyalty points</strong> have been restored to your account.
      @endif
    </p>
  </div>
  @endif

  <!-- Order Value -->
  <table class="totals-table">
    <tr>
      <td class="label-col">Order Total (Cancelled)</td>
      <td class="value-col" style="text-decoration:line-through;color:#999;">₹{{ number_format($order->total, 2) }}</td>
    </tr>
  </table>

  <!-- CTA -->
  <div class="cta-wrapper">
    <a href="http://localhost:3000" class="cta-button">Continue Shopping</a>
  </div>

  <div class="alert-box alert-warning">
    <p>❓ <strong>Cancelled by mistake?</strong> Feel free to place a new order anytime or reply to this email if you believe this was done in error. We are happy to help!</p>
  </div>

</div>

@endsection
