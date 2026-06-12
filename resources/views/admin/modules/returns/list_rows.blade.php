@forelse($items as $item)
<tr>
    <!-- Serial Number -->
    <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>

    <!-- Order Number -->
    <td>
        <a href="javascript:void(0)" onclick="viewReturnDetails({{ $item->id }})" class="text-primary fw-semibold">
            #{{ $item->order->order_number }}
        </a>
    </td>

    <!-- Customer -->
    <td>
        <strong>{{ $item->user->name }}</strong><br>
        <small class="text-muted">{{ $item->user->email }}</small>
    </td>

    <!-- Reason -->
    <td>
        <span title="{{ $item->reason }}">{{ Str::limit($item->reason, 50) }}</span>
    </td>

    <!-- Images -->
    
    <!-- Status Dropdown -->
    <td>
        <select class="form-select form-select-sm return-status-select"
                data-return-id="{{ $item->id }}"
                onchange="updateReturnStatus({{ $item->id }}, this.value)">
            <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ $item->status == 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ $item->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
    </td>

    <!-- Date -->
    <td>{{ $item->created_at->format('d M Y') }}</td>

    <!-- Actions -->
    <td>
        <div class="d-flex align-items-center justify-content-start gap-10">
            <!-- View -->
            <button type="button" class="table__icon edit" onclick="viewReturnDetails({{ $item->id }})" title="View Details">
                <i class="fa-regular fa-eye"></i>
            </button>

            <!-- Delete -->
            <button class="removeBtn table__icon delete" onclick="deleteRecord('{{ $item->id }}', $(this))" title="Delete Return">
                <i class="fa-regular fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="7" align="center">No Return Requests Found!</td>
</tr>
@endforelse

<script>
function updateReturnStatus(returnId, status) {
    if (status === 'rejected') {
        Swal.fire({
            title: 'Reject Return',
            input: 'textarea',
            inputLabel: 'Please enter the reason for rejection',
            inputPlaceholder: 'e.g., Item not eligible for return or policy violation',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'You must enter a reason to reject the return!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                sendReturnStatusUpdate(returnId, status, result.value);
            } else {
                recordList();
            }
        });
    } else {
        if (confirm('Are you sure you want to update this return status to ' + status + '?')) {
            sendReturnStatusUpdate(returnId, status, null);
        } else {
            recordList();
        }
    }
}

function sendReturnStatusUpdate(returnId, status, adminNote = null) {
    $.ajax({
        url: '{{ route("returns.update-status", ":id") }}'.replace(':id', returnId),
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
            toastr.error('Error updating return status');
        }
    });
}
</script>
