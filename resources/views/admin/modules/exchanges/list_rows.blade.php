@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Order Number -->
    <td>
        <a href="javascript:void(0)" onclick="viewExchangeDetails({{ $item->id }})" class="text-primary fw-semibold">
            #{{ $item->order->order_number }}
        </a>
    </td>

    <!-- Customer -->
    <td>
        <strong>{{ $item->order->name }}</strong><br>
        <small class="text-muted">{{ $item->order->email }}</small>
    </td>

    <!-- Exchange Details -->
    <td>
        <div class="text-danger" style="font-size: 12px;">
            <strong>From:</strong> {{ $item->original_size }}{{ $item->original_color ? ' - ' . $item->original_color : '' }}
        </div>
        <div class="text-success" style="font-size: 12px;">
            <strong>To:</strong> {{ $item->exchange_size }}{{ $item->exchange_color ? ' - ' . $item->exchange_color : '' }}
        </div>
    </td>

    <!-- Status Dropdown -->
    <td>
        <select class="form-select form-select-sm exchange-status-select"
                data-exchange-id="{{ $item->id }}"
                onchange="updateExchangeStatus({{ $item->id }}, this.value)">
            <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ $item->status == 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ $item->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="pickup_scheduled" {{ $item->status == 'pickup_scheduled' ? 'selected' : '' }}>Pickup Scheduled</option>
            <option value="picked_up" {{ $item->status == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
            <option value="exchange_shipped" {{ $item->status == 'exchange_shipped' ? 'selected' : '' }}>Exchange Shipped</option>
            <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ $item->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </td>

    <!-- Payment Status -->
    <td>
        @if($item->payment_status == 'paid')
            <span class="badge bg-success">Paid</span>
        @elseif($item->payment_status == 'pending')
            <span class="badge bg-warning">Pending</span>
        @elseif($item->payment_status == 'failed')
            <span class="badge bg-danger">Failed</span>
        @else
            <span class="badge bg-secondary">{{ ucfirst($item->payment_status) }}</span>
        @endif
        <br>
        <small class="text-muted">₹{{ number_format($item->exchange_charge, 2) }}</small>
    </td>

    <!-- Date -->
    <td>{{ $item->created_at->format('d M Y') }}</td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-5">
            <!-- View -->
            <button type="button" class="table__icon edit" onclick="viewExchangeDetails({{ $item->id }})" title="View Details">
                <i class="fa-regular fa-eye"></i>
            </button>

            @if($item->status == 'approved' && $item->payment_status == 'paid')
                <!-- Schedule Pickup -->
                <button type="button" class="btn btn-sm btn-primary" onclick="schedulePickup({{ $item->id }})" title="Schedule Pickup">
                    <i class="fa fa-truck"></i> Pickup
                </button>
            @endif

            @if($item->status == 'picked_up')
                <!-- Schedule Delivery -->
                <button type="button" class="btn btn-sm btn-success" onclick="scheduleDelivery({{ $item->id }})" title="Schedule Delivery">
                    <i class="fa fa-shipping-fast"></i> Deliver
                </button>
            @endif

            @if($item->status == 'exchange_shipped')
                <!-- Mark Completed -->
                <button type="button" class="btn btn-sm btn-info" onclick="markCompleted({{ $item->id }})" title="Mark Completed">
                    <i class="fa fa-check"></i> Complete
                </button>
            @endif

            <!-- Delete -->
            <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $item->id }}', $(this))" title="Delete Exchange">
                <i class="fa-regular fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="8" align="center">No Exchange Requests Found!</td>
</tr>
@endforelse

<script>
function updateExchangeStatus(exchangeId, status) {
    if (status === 'rejected') {
        Swal.fire({
            title: 'Reject Exchange',
            input: 'textarea',
            inputLabel: 'Please enter the reason for rejection',
            inputPlaceholder: 'e.g., Product not eligible for exchange or policy violation',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'You must enter a reason to reject the exchange!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                sendExchangeStatusUpdate(exchangeId, status, result.value);
            } else {
                recordList();
            }
        });
    } else {
        if (confirm('Are you sure you want to update this exchange status to ' + status + '?')) {
            sendExchangeStatusUpdate(exchangeId, status, null);
        } else {
            recordList();
        }
    }
}

function sendExchangeStatusUpdate(exchangeId, status, adminNote = null) {
    $.ajax({
        url: '{{ route("exchanges.update-status", ":id") }}'.replace(':id', exchangeId),
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: status,
            admin_note: adminNote
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                recordList();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error updating exchange status');
        }
    });
}

// Schedule Pickup via Shiprocket
function schedulePickup(exchangeId) {
    if (!confirm('Schedule pickup for this exchange? This will create a Shiprocket pickup order.')) {
        return;
    }

    $.ajax({
        url: '/admin/exchanges-schedule-pickup/' + exchangeId,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        beforeSend: function() {
            toastr.info('Creating Shiprocket pickup order...');
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Pickup scheduled successfully! AWB: ' + (response.data.shiprocket_pickup_awb_code || 'Pending'));
                recordList();
            } else {
                toastr.error(response.message || 'Failed to schedule pickup');
            }
        },
        error: function(xhr) {
            let message = 'Error scheduling pickup';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
            console.error('Pickup error:', xhr.responseJSON);
        }
    });
}

// Schedule Delivery via Shiprocket
function scheduleDelivery(exchangeId) {
    if (!confirm('Schedule delivery for the exchanged product? This will create a Shiprocket delivery order.')) {
        return;
    }

    $.ajax({
        url: '/admin/exchanges-schedule-delivery/' + exchangeId,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        beforeSend: function() {
            toastr.info('Creating Shiprocket delivery order...');
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Delivery scheduled successfully! AWB: ' + (response.data.shiprocket_delivery_awb_code || 'Pending'));
                recordList();
            } else {
                toastr.error(response.message || 'Failed to schedule delivery');
            }
        },
        error: function(xhr) {
            let message = 'Error scheduling delivery';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            toastr.error(message);
            console.error('Delivery error:', xhr.responseJSON);
        }
    });
}

// Mark Exchange as Completed
function markCompleted(exchangeId) {
    if (!confirm('Mark this exchange as completed?')) {
        return;
    }

    $.ajax({
        url: '/admin/exchanges-mark-completed/' + exchangeId,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Exchange marked as completed!');
                recordList();
            } else {
                toastr.error(response.message || 'Failed to mark as completed');
            }
        },
        error: function(xhr) {
            toastr.error('Error marking exchange as completed');
        }
    });
}
</script>
