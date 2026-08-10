@component('mail::message')
# 🛍️ New Order Received!

A new order **#{{ $order->order_number }}** has been placed on {{ config('app.name') }}.

**Customer Details:**
- Name: {{ $order->name }}
- Email: {{ $order->email }}
- Phone: {{ $order->phone }}
- Address: {{ $order->address }}, {{ $order->city }} - {{ $order->zip }}

**Order Summary:**
- Subtotal: ₹{{ number_format($order->subtotal, 2) }}
- Discount: ₹{{ number_format($order->discount, 2) }}
- Shipping: ₹{{ number_format($order->shipping_charge, 2) }}
- **Total Amount: ₹{{ number_format($order->total, 2) }}**
- Payment Method: {{ strtoupper($order->payment_method) }}
- Payment Status: {{ ucfirst($order->payment_status) }}

**Items:**
@foreach($order->items as $item)
- {{ $item['name'] }} (Qty: {{ $item['quantity'] }}) - ₹{{ number_format($item['price'], 2) }}
@endforeach

Thanks,<br>
**{{ config('app.name') }} Automated System**
@endcomponent
