@extends('admin.layouts.app')

@push('meta')
<title>Active User Carts | {{ config('app.name') }}</title>
<meta content="Active User Carts & Abandoned Cart Recovery" name="description" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Active User Carts</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Stats Summary Row -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 rounded-3 text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs text-uppercase tracking-wider text-muted mb-1" style="color: #94a3b8 !important;">Active Carts</div>
                        <h3 class="mb-0 fw-bold text-white">{{ count($activeCarts) }}</h3>
                    </div>
                    <div class="p-3 bg-white bg-opacity-10 rounded-circle text-warning fs-3">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 rounded-3 text-white" style="background: linear-gradient(135deg, #065f46 0%, #047857 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs text-uppercase tracking-wider text-muted mb-1" style="color: #a7f3d0 !important;">Potential Cart Value</div>
                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($totalPotentialRevenue, 2) }}</h3>
                    </div>
                    <div class="p-3 bg-white bg-opacity-10 rounded-circle text-emerald fs-3">
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 rounded-3 text-white" style="background: linear-gradient(135deg, #312e81 0%, #4338ca 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs text-uppercase tracking-wider text-muted mb-1" style="color: #c7d2fe !important;">Total Items in Carts</div>
                        <h3 class="mb-0 fw-bold text-white">{{ array_sum(array_column($activeCarts, 'total_items')) }}</h3>
                    </div>
                    <div class="p-3 bg-white bg-opacity-10 rounded-circle text-indigo fs-3">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card shadow-sm border-0 mb-4 p-3 bg-white rounded-3">
        <form method="GET" action="{{ route('admin.active-carts.index') }}" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label class="form-label text-xs text-uppercase fw-bold text-secondary mb-1">Search Customer</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, Email or Phone..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label text-xs text-uppercase fw-bold text-secondary mb-1">Filter by Product</label>
                <select name="product_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" {{ !$productId ? 'selected' : '' }}>All Products in Carts</option>
                    @foreach($productsInCarts as $prod)
                        <option value="{{ $prod->id }}" {{ $productId == $prod->id ? 'selected' : '' }}>
                            {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-xs text-uppercase fw-bold text-secondary mb-1">User Type</label>
                <select name="user_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ $userType == 'all' ? 'selected' : '' }}>All Users</option>
                    <option value="registered" {{ $userType == 'registered' ? 'selected' : '' }}>Registered Users</option>
                    <option value="guest" {{ $userType == 'guest' ? 'selected' : '' }}>Guest Sessions</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-xs text-uppercase fw-bold text-secondary mb-1">Min Cart Value (₹)</label>
                <select name="min_amount" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="0" {{ $minAmount == 0 ? 'selected' : '' }}>All Amounts</option>
                    <option value="500" {{ $minAmount == 500 ? 'selected' : '' }}>Above ₹500</option>
                    <option value="1000" {{ $minAmount == 1000 ? 'selected' : '' }}>Above ₹1,000</option>
                    <option value="2000" {{ $minAmount == 2000 ? 'selected' : '' }}>Above ₹2,000</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-dark flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="{{ route('admin.active-carts.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Active User Carts Table -->
    <div class="row">
        <div class="col-xxl-12">
            <div class="card__wrapper shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-cart-flatbed text-primary me-2"></i>Real-Time Active User Carts</h5>
                        <span class="badge bg-light text-dark border">{{ count($activeCarts) }} Active Carts</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="bulkSendBtn" class="btn btn-sm btn-success d-none" onclick="openBulkModal()">
                            <i class="fa-solid fa-paper-plane me-1"></i> Bulk Send Coupon (<span id="selectedCount">0</span>)
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload();">
                            <i class="fa-solid fa-rotate-right me-1"></i> Refresh Realtime
                        </button>
                    </div>
                </div>

                <div class="table__wrapper table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-secondary">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input" onchange="toggleSelectAll(this)">
                                </th>
                                <th>User Info</th>
                                <th style="min-width: 280px;">Cart Items</th>
                                <th>Total Quantity</th>
                                <th>Balance Cart Value</th>
                                <th>Offers Sent Count</th>
                                <th>Last Activity</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeCarts as $cart)
                                <tr>
                                    <td>
                                        @if($cart['user_email'])
                                            <input type="checkbox" class="form-check-input cart-user-checkbox" value="{{ $cart['user_email'] }}" onchange="updateSelectedCount()">
                                        @else
                                            <input type="checkbox" class="form-check-input" disabled title="No email captured">
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar rounded-circle bg-light d-flex align-items-center justify-content-center text-dark fw-bold" style="width: 40px; height: 40px; border: 1px solid #e2e8f0;">
                                                {{ strtoupper(substr($cart['user_name'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $cart['user_name'] }}</div>
                                                @if($cart['user_email'])
                                                    <div class="text-muted small"><i class="fa-regular fa-envelope me-1"></i>{{ $cart['user_email'] }}</div>
                                                @endif
                                                @if($cart['user_phone'])
                                                    <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i>{{ $cart['user_phone'] }}</div>
                                                @endif
                                                @if($cart['is_guest'])
                                                    <span class="badge bg-secondary opacity-75">Guest Session</span>
                                                @else
                                                    <span class="badge bg-success opacity-75">Registered User</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($cart['items'] as $item)
                                                <div class="d-flex align-items-center gap-2 p-1 bg-light rounded-2 border">
                                                    @if($item['image'])
                                                        <img src="{{ $item['image'] }}" alt="" style="width: 38px; height: 38px; object-fit: cover; border-radius: 4px;">
                                                    @endif
                                                    <div class="flex-grow-1" style="font-size: 0.82rem;">
                                                        <div class="fw-semibold text-dark">{{ $item['product_name'] }}</div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                            @if($item['size'])<span class="me-2">Size: <strong>{{ $item['size'] }}</strong></span>@endif
                                                            @if($item['color'])<span>Color: <strong>{{ $item['color'] }}</strong></span>@endif
                                                        </div>
                                                    </div>
                                                    <div class="text-end px-2" style="font-size: 0.82rem;">
                                                        <div>Qty: <strong>{{ $item['quantity'] }}</strong></div>
                                                        <div class="fw-bold text-dark">₹{{ number_format($item['line_total'], 2) }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark fs-6 px-3 py-2">{{ $cart['total_items'] }} Items</span>
                                    </td>
                                    <td>
                                        <div class="fs-5 fw-bold text-emerald" style="color: #059669;">₹{{ number_format($cart['cart_total'], 2) }}</div>
                                    </td>
                                    <td>
                                        @if($cart['sent_count'] > 0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-7 fw-bold" id="sentBadge-{{ md5($cart['user_email']) }}">
                                                <i class="fa-solid fa-envelope-circle-check me-1"></i> Sent {{ $cart['sent_count'] }} {{ $cart['sent_count'] == 1 ? 'time' : 'times' }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border px-2 py-1 fs-7 fw-normal" id="sentBadge-{{ md5($cart['user_email'] ?? '') }}">
                                                Not Sent Yet
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-clock me-1"></i>{{ $cart['last_updated'] }}
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        @if($cart['user_email'])
                                            <button 
                                                class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm"
                                                onclick="openSendCouponModal('{{ $cart['user_email'] }}', '{{ addslashes($cart['user_name']) }}')"
                                            >
                                                <i class="fa-solid fa-ticket-alt"></i> Send Coupon Email
                                            </button>
                                        @else
                                            <span class="text-muted small italic">No email captured</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-cart-arrow-down fs-1 mb-3 d-block text-secondary"></i>
                                        <h5>No Active User Carts Found</h5>
                                        <p class="mb-0">When customers add products to their shopping cart, their live cart balance will appear here in real-time!</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single Send Coupon Modal -->
<div class="modal fade" id="sendCouponModal" tabindex="-1" aria-labelledby="sendCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold text-white" id="sendCouponModalLabel">
                    <i class="fa-solid fa-paper-plane me-2 text-warning"></i>Send Coupon Offer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sendCouponForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase fs-7">Recipient Email</label>
                        <input type="email" id="modalUserEmail" name="email" class="form-control bg-light" readonly required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase fs-7">Select Coupon Code</label>
                        <select id="modalCouponId" name="coupon_id" class="form-select" required>
                            <option value="" disabled selected>Choose a coupon to assign...</option>
                            @foreach($coupons as $coupon)
                                <option value="{{ $coupon->id }}">
                                    {{ $coupon->code }} - {{ $coupon->type == 'percentage' ? intval($coupon->value) . '% OFF' : '₹' . intval($coupon->value) . ' OFF' }}
                                    (Min Cart: ₹{{ number_format($coupon->min_cart_amount, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase fs-7">Personalized Message</label>
                        <textarea id="modalCustomMessage" name="custom_message" class="form-control" rows="3" placeholder="Enter custom message to encourage customer to complete purchase...">We noticed you left some amazing items in your cart! Here is an exclusive coupon code to complete your order with extra savings.</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitSendBtn" class="btn btn-primary px-4 fw-bold">
                        <i class="fa-solid fa-envelope me-1"></i> Send Coupon Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Send Coupon Modal -->
<div class="modal fade" id="bulkCouponModal" tabindex="-1" aria-labelledby="bulkCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold text-white" id="bulkCouponModalLabel">
                    <i class="fa-solid fa-paper-plane me-2"></i>Bulk Send Coupon Offers (<span id="modalBulkCount">0</span> Users)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkCouponForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase fs-7">Selected Recipient Emails</label>
                        <div id="bulkEmailPills" class="p-2 bg-light border rounded d-flex flex-wrap gap-1" style="max-height: 100px; overflow-y: auto;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase fs-7">Select Coupon Code</label>
                        <select id="bulkCouponId" name="coupon_id" class="form-select" required>
                            <option value="" disabled selected>Choose a coupon code for bulk offer...</option>
                            @foreach($coupons as $coupon)
                                <option value="{{ $coupon->id }}">
                                    {{ $coupon->code }} - {{ $coupon->type == 'percentage' ? intval($coupon->value) . '% OFF' : '₹' . intval($coupon->value) . ' OFF' }}
                                    (Min Cart: ₹{{ number_format($coupon->min_cart_amount, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase fs-7">Personalized Message</label>
                        <textarea id="bulkCustomMessage" name="custom_message" class="form-control" rows="3" placeholder="Enter custom message for selected cart users...">We noticed you left some amazing items in your cart! Here is an exclusive coupon code to complete your order with extra savings.</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitBulkBtn" class="btn btn-success px-4 fw-bold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Send Bulk Coupon Emails
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll(master) {
    $('.cart-user-checkbox').prop('checked', master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    var checked = $('.cart-user-checkbox:checked');
    var count = checked.length;
    $('#selectedCount').text(count);
    if (count > 0) {
        $('#bulkSendBtn').removeClass('d-none');
    } else {
        $('#bulkSendBtn').addClass('d-none');
    }
}

function openSendCouponModal(email, userName) {
    $('#modalUserEmail').val(email);
    var modal = new bootstrap.Modal(document.getElementById('sendCouponModal'));
    modal.show();
}

function openBulkModal() {
    var checked = $('.cart-user-checkbox:checked');
    if (checked.length === 0) {
        Swal.fire('No Users Selected', 'Please select at least one cart user to send coupon emails.', 'warning');
        return;
    }

    var emails = [];
    var pillsHtml = '';
    checked.each(function() {
        var email = $(this).val();
        emails.push(email);
        pillsHtml += '<span class="badge bg-dark text-white p-2 fs-7">' + email + '</span>';
    });

    $('#modalBulkCount').text(emails.length);
    $('#bulkEmailPills').html(pillsHtml);
    var modal = new bootstrap.Modal(document.getElementById('bulkCouponModal'));
    modal.show();
}

// Single Send Form Submit
$('#sendCouponForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $('#submitSendBtn');
    var originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...');

    $.ajax({
        url: "{{ route('admin.active-carts.send-coupon') }}",
        method: "POST",
        data: $(this).serialize(),
        success: function(response) {
            btn.prop('disabled', false).html(originalText);
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('sendCouponModal')).hide();
                Swal.fire({
                    title: 'Email Sent!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#10b981'
                }).then(function() {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to send coupon email.',
                    icon: 'error'
                });
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html(originalText);
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred while sending email.';
            Swal.fire({
                title: 'Error',
                text: msg,
                icon: 'error'
            });
        }
    });
});

// Bulk Send Form Submit
$('#bulkCouponForm').on('submit', function(e) {
    e.preventDefault();
    var checked = $('.cart-user-checkbox:checked');
    var emails = [];
    checked.each(function() { emails.push($(this).val()); });

    if (emails.length === 0) {
        Swal.fire('Error', 'No emails selected.', 'error');
        return;
    }

    var btn = $('#submitBulkBtn');
    var originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending Bulk Emails...');

    var postData = {
        _token: "{{ csrf_token() }}",
        emails: emails,
        coupon_id: $('#bulkCouponId').val(),
        custom_message: $('#bulkCustomMessage').val()
    };

    $.ajax({
        url: "{{ route('admin.active-carts.bulk-send-coupon') }}",
        method: "POST",
        data: postData,
        success: function(response) {
            btn.prop('disabled', false).html(originalText);
            if (response.success) {
                bootstrap.Modal.getInstance(document.getElementById('bulkCouponModal')).hide();
                Swal.fire({
                    title: 'Bulk Email Sent!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#10b981'
                }).then(function() {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to send bulk coupon emails.',
                    icon: 'error'
                });
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html(originalText);
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred during bulk email send.';
            Swal.fire({
                title: 'Error',
                text: msg,
                icon: 'error'
            });
        }
    });
});
</script>
@endpush
