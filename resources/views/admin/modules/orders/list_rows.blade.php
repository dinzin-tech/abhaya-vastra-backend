@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Order Number -->
    <td><strong>{{ $item->order_number }}</strong></td>

    <!-- Customer Name -->
    <td>{{ $item->name }}</td>

    <!-- Email -->
    <td>{{ $item->email }}</td>

    <!-- Phone -->
    <td>{{ $item->phone }}</td>

    <td>{{ $item->address }}</td>


    <!-- Total -->
    <td>${{ number_format($item->total, 2) }}</td>

    <!-- Payment Method -->
    <td>
        @if($item->payment_method == 'razorpay')
            <span class="badge bg-info">Razorpay</span>
        @else
            <span class="badge bg-secondary">COD</span>
        @endif
    </td>

    <!-- Payment Status -->
    <td>
        @if($item->payment_status == 'completed')
            <span class="badge bg-success">Paid</span>
        @elseif($item->payment_status == 'failed')
            <span class="badge bg-danger">Failed</span>
        @else
            <span class="badge bg-warning">Pending</span>
        @endif
    </td>

    <!-- Order Status -->
    <td>
        <select class="form-select form-select-sm status-select" data-order-id="{{ $item->id }}" onchange="updateOrderStatus({{ $item->id }}, this.value)">
            <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="processing" {{ $item->status == 'processing' ? 'selected' : '' }}>Processing</option>
            <option value="shipped" {{ $item->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
            <option value="delivered" {{ $item->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
            <option value="cancelled" {{ $item->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </td>

    <!-- Date -->
    <td>{{ $item->created_at->format('d M Y') }}</td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <!-- View Details -->
            <button type="button" class="table__icon edit" onclick="viewOrderDetails({{ $item->id }})" title="View Details">
                <i class="fa-regular fa-eye"></i>
            </button>
            <!-- Delete -->
            <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $item->id }}', $(this))" title="Delete Order">
                <i class="fa-regular fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="11" align="center">No Orders Found!</td>
</tr>
@endforelse

<script>
function updateOrderStatus(orderId, status) {
    if(confirm('Are you sure you want to update this order status to ' + status + '?')) {
        $.ajax({
            url: '{{ route("orders.update-status") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order_id: orderId,
                status: status
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    recordList(); // Reload the list
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Error updating order status');
            }
        });
    } else {
        // Reload to reset the dropdown
        recordList();
    }
}
</script>
