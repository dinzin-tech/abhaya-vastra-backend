@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Payment ID -->
    <td><strong>{{ $item->razorpay_order_id }}</strong></td>
    <td><strong>{{ $item->razorpay_payment_id }}</strong></td>


    

    <!-- Order Number -->
    <td>{{ $item->order->order_number ?? '-' }}</td>

    <!-- Customer Name -->
    <td>{{ $item->user->name ?? $item->order->name ?? '-' }}</td>

    <!-- Amount -->
    <td>${{ number_format($item->amount, 2) }}</td>

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
        @if($item->status == 'completed')
            <span class="badge bg-success">Completed</span>
        @elseif($item->status == 'failed')
            <span class="badge bg-danger">Failed</span>
        @elseif($item->status == 'processing')
            <span class="badge bg-warning">Processing</span>
        @else
            <span class="badge bg-secondary">Pending</span>
        @endif
    </td>

    <!-- Date -->
    <td>{{ $item->created_at->format('d M Y') }}</td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <!-- View Details -->
            <!-- <a type="button" class="table__icon edit" href="{{ route('payments.show', $item->id) }}" title="View Details">
                <i class="fa-regular fa-eye"></i>
            </a> -->
            <!-- Delete -->
            <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $item->id }}', $(this))" title="Delete Payment">
                <i class="fa-regular fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="9" align="center">No Payments Found!</td>
</tr>
@endforelse
