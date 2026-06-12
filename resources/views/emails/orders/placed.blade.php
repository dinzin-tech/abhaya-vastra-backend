@component('mail::message')
# Thank you for your order, {{ $order->name }}!

**Order Number:** {{ $order->order_number }}

**Shipping Details:**
- Name: {{ $order->name }}
- Email: {{ $order->email }}
- Phone: {{ $order->phone }}
- Address: {{ $order->address }}, {{ $order->city }} - {{ $order->zip }}

**Items Ordered:**
@foreach($order->items as $item)
- {{ $item['name'] }} (Qty: {{ $item['quantity'] }}) - ${{ number_format($item['price'], 2) }}
@endforeach

**Subtotal:** ${{ number_format($order->subtotal, 2) }}  
**Discount:** ${{ number_format($order->discount, 2) }}  
**Total:** ${{ number_format($order->total, 2) }}

**Payment Method:** {{ ucfirst($order->payment_method) }}  
**Payment Status:** {{ ucfirst($order->payment_status) }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
