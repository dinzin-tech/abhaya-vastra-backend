@extends('admin.layouts.app')
@push('meta')
<title>Orders | {{ config('app.name') }}</title>
<meta content="Orders" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Orders</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- ✅ Search Filters -->
        <div class="col-xxl-12">
            <div class="row">
                <div class="col-xxl-3">
                    <div class="card__wrapper">
                        <div class="from__input-box">
                            <div class="form__input">
                                <input type="text" class="form-control" id="order_number" name="order_number" 
                                    value="{{ $order_number ?? '' }}" placeholder="Order Number">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3">
                    <div class="card__wrapper">
                        <div class="from__input-box">
                            <div class="form__input">
                                <input type="text" class="form-control" id="name" name="name" 
                                value="{{$name ? $name : ''}}" placeholder="Customer Name">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3">
                    <div class="card__wrapper">
                        <div class="from__input-box">
                            <div class="form__input">
                                <input type="text" class="form-control" id="phone" name="phone" 
                                    value="{{ $phone ? $phone : '' }}" placeholder="Mobile Number">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3">
                <div class="card__wrapper">
                        <div class="d-flex align-items-center justify-content-between gap-15">
                            <button type="button" class="btn btn-secondary w-100" onclick="recordList()">Search</button>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-xxl-12 mt-3">
                    <div class="card__wrapper">
                        <div class="d-flex align-items-center justify-content-between gap-15">
                            <button type="button" class="btn btn-secondary w-100" onclick="recordList()">Search</button>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>

        <!-- ✅ Table -->
        <div class="col-xxl-12 mt-3">
            <div class="card__wrapper">
                <div class="mb-10">
                    <div class="row align-items-center">
                        <div class="col-xl-8 order-2 order-xl-1">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <select class="form-control" name="offset" id="offset" onchange="recordList()">
                                        <option value="10" {{ $offset == '10' ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ $offset == '25' ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ $offset == '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ $offset == '100' ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table__wrapper table-responsive">
                    <table class="table mb-20 multiple_tables" id="record-list" data-url="{{route('orders.list')}}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>Sr. No.</th>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Total</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table__body"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="11" class="text-right"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsBody">
                <div class="text-center py-5">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@stop

@push('appendJs')
<script src="{{ asset('assets/js/vendor/jquery-3.7.0.js') }}"></script>
<script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<!-- <script src="{{ asset('assets/js/main.js') }}"></script> -->

<script type="text/javascript">
    var searchUrl = "{{ route('orders.list') }}";
    var listUrl = "{{ route('orders.index') }}";
    var deleteUrl = "{{ route('orders.delete') }}";
    var tblObj = $("#record-list");

   
    $(document).ready(function() {
        recordList();
    });

    // View Order Details in Modal
    function viewOrderDetails(orderId) {
        // Show modal using Bootstrap 5
        var myModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        myModal.show();
        
        // Show loading spinner
        $('#orderDetailsBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        // Fetch order details
        $.ajax({
            url: '/admin/orders-details/' + orderId,
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    displayOrderDetails(response.data);
                } else {
                    $('#orderDetailsBody').html('<div class="alert alert-danger">Failed to load order details</div>');
                }
            },
            error: function(xhr) {
                $('#orderDetailsBody').html('<div class="alert alert-danger">Error loading order details</div>');
            }
        });
    }

    function displayOrderDetails(order) {
        let items = typeof order.items === 'string' ? JSON.parse(order.items) : order.items;
        
        let itemsHtml = '';
        items.forEach(function(item, index) {
            let designLink = '';
            if (item.custom_design_url) {
                designLink = `<br><a href="${item.custom_design_url}" target="_blank" class="badge text-white mt-1 me-1" style="background-color: #6366f1; text-decoration: none; padding: 4px 8px; font-size: 0.72rem; border-radius: 4px;"><i class="fa-solid fa-paint-brush"></i> View Design File</a>`;
            }
            if (item.custom_preview_url) {
                designLink += `<a href="${item.custom_preview_url}" target="_blank" class="badge text-white mt-1" style="background-color: #3b82f6; text-decoration: none; padding: 4px 8px; font-size: 0.72rem; border-radius: 4px;"><i class="fa-solid fa-shirt"></i> View Mockup</a>`;
            }
            itemsHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.name}${designLink}</td>
                    <td>₹${parseFloat(item.price).toFixed(2)}</td>
                    <td>${item.quantity || 1}</td>
                    <td>${item.size || item.selectedSize || 'N/A'}</td>
                    <td>${item.color || item.selectedColor || 'N/A'}</td>
                    <td><strong>₹${(item.price * item.quantity).toFixed(2)}</strong></td>
                </tr>
            `;
        });
        
        let paymentStatusBadge = '';
        if(order.payment_status === 'completed') {
            paymentStatusBadge = '<span class="badge bg-success">Paid</span>';
        } else if(order.payment_status === 'failed') {
            paymentStatusBadge = '<span class="badge bg-danger">Failed</span>';
        } else {
            paymentStatusBadge = '<span class="badge bg-warning">Pending</span>';
        }
        
        let orderStatusBadge = '';
        switch(order.status) {
            case 'pending':
                orderStatusBadge = '<span class="badge bg-warning">Pending</span>';
                break;
            case 'processing':
                orderStatusBadge = '<span class="badge bg-info">Processing</span>';
                break;
            case 'shipped':
                orderStatusBadge = '<span class="badge bg-primary">Shipped</span>';
                break;
            case 'delivered':
                orderStatusBadge = '<span class="badge bg-success">Delivered</span>';
                break;
            case 'cancelled':
                orderStatusBadge = '<span class="badge bg-danger">Cancelled</span>';
                break;
            default:
                orderStatusBadge = '<span class="badge bg-secondary">' + order.status + '</span>';
        }

        // Shiprocket delivery management block
        let shiprocketHtml = '';
        if (order.status !== 'cancelled') {
            shiprocketHtml = `
                <div class="card border-dark mb-4 mt-4 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 px-3">
                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-truck me-2"></i> Shiprocket Delivery Control Panel</h6>
                        ${order.shiprocket_order_id ? '<span class="badge bg-success">Connected</span>' : '<span class="badge bg-secondary">Unconnected</span>'}
                    </div>
                    <div class="card-body p-3">
            `;

            if (!order.shiprocket_order_id) {
                shiprocketHtml += `
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <p class="mb-0 text-muted small">This shipment has not been registered in Shiprocket yet.</p>
                            </div>
                            <button class="btn btn-primary btn-sm" id="btn-sr-create" onclick="shiprocketCreateShipment(${order.id})">
                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Register Order with Shiprocket
                            </button>
                        </div>
                `;
            } else if (!order.shiprocket_awb_code) {
                shiprocketHtml += `
                        <div class="row g-3">
                            <div class="col-md-6 border-end">
                                <p class="mb-1 small"><strong>Shiprocket Order ID:</strong> ${order.shiprocket_order_id}</p>
                                <p class="mb-2 small"><strong>Shiprocket Shipment ID:</strong> ${order.shiprocket_shipment_id || 'N/A'}</p>
                                <button class="btn btn-outline-danger btn-xs py-1" onclick="shiprocketCancelShipment(${order.id})">
                                    <i class="fa-solid fa-ban me-1"></i> Cancel & Reset Order
                                </button>
                            </div>
                            <div class="col-md-6 ps-md-4">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted">Select Courier Partner:</h6>
                                <div id="couriers-loading-section">
                                    <button class="btn btn-sm btn-secondary w-100" onclick="shiprocketLoadCouriers(${order.id})">
                                        <i class="fa-solid fa-search me-1"></i> Search Available Couriers
                                    </button>
                                </div>
                                <div id="couriers-selection-section" style="display:none;">
                                    <div class="mb-2">
                                        <select class="form-select form-select-sm" id="sr-courier-select">
                                            <!-- couriers loaded dynamically -->
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-success btn-sm w-100" onclick="shiprocketAssignAwb(${order.id})">
                                            <i class="fa-solid fa-truck-fast me-1"></i> Book Courier
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="shiprocketLoadCouriers(${order.id})">
                                            <i class="fa-solid fa-rotate-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                `;
            } else {
                shiprocketHtml += `
                        <div class="row g-3">
                            <div class="col-md-6 border-end">
                                <p class="mb-1 small"><strong>Shiprocket Order ID:</strong> ${order.shiprocket_order_id}</p>
                                <p class="mb-1 small"><strong>Shiprocket Shipment ID:</strong> ${order.shiprocket_shipment_id || 'N/A'}</p>
                                <p class="mb-1 small"><strong>Courier:</strong> ${order.shiprocket_courier_name}</p>
                                <p class="mb-0 small"><strong>AWB (Tracking):</strong> <code class="bg-dark text-white px-2 py-0.5 rounded">${order.shiprocket_awb_code}</code></p>
                            </div>
                            <div class="col-md-6 ps-md-4 d-flex flex-column gap-2 justify-content-center">
                                <button class="btn btn-outline-primary btn-sm text-start" onclick="shiprocketGenerateLabel(${order.id})">
                                    <i class="fa-solid fa-file-pdf me-2"></i> Download Shipping Label
                                </button>
                                <button class="btn btn-outline-info btn-sm text-start" onclick="shiprocketGenerateManifest(${order.id})">
                                    <i class="fa-solid fa-file-invoice me-2"></i> Download Shipment Manifest
                                </button>
                                <button class="btn btn-outline-danger btn-sm text-start" onclick="shiprocketCancelShipment(${order.id})">
                                    <i class="fa-solid fa-circle-xmark me-2"></i> Cancel Shipment & Release AWB
                                </button>
                            </div>
                        </div>
                `;
            }

            shiprocketHtml += `
                    </div>
                </div>
                <hr>
            `;
        }

        // Qikink POD management block
        let qikinkHtml = '';
        if (order.has_qikink_items && order.status !== 'cancelled') {
            qikinkHtml = `
                <div class="card border-primary mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: #4f46e5 !important;">
                        <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-shirt me-2"></i> Qikink Print on Demand Fulfillments</h6>
                        ${order.qikink_order_id ? '<span class="badge bg-success">Pushed</span>' : '<span class="badge bg-secondary">Not Pushed</span>'}
                    </div>
                    <div class="card-body p-3">
            `;

            if (!order.qikink_order_id) {
                qikinkHtml += `
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <p class="mb-0 text-muted small">This order has POD items but has not been pushed to Qikink yet.</p>
                            </div>
                            <button class="btn btn-primary btn-sm" id="btn-qikink-create" onclick="qikinkCreateOrder(${order.id})">
                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Send Order to Qikink
                            </button>
                        </div>
                `;
            } else {
                qikinkHtml += `
                        <div class="row g-3">
                            <div class="col-md-6 border-end">
                                <p class="mb-1 small"><strong>Qikink Order ID:</strong> ${order.qikink_order_id}</p>
                                <p class="mb-1 small"><strong>Qikink Status:</strong> <span class="badge bg-info text-dark font-monospace">${order.qikink_status || 'Queued'}</span></p>
                                <p class="mb-1 small"><strong>Sent On:</strong> ${order.qikink_sent_at ? new Date(order.qikink_sent_at).toLocaleString() : 'N/A'}</p>
                                ${order.qikink_awb_code ? `<p class="mb-1 small"><strong>AWB Code:</strong> <code class="bg-dark text-white px-2 py-0.5 rounded">${order.qikink_awb_code}</code></p>` : ''}
                                ${order.qikink_tracking_url ? `<p class="mb-0 small"><strong>Tracking Link:</strong> <a href="${order.qikink_tracking_url}" target="_blank" class="text-primary">Track Shipment <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a></p>` : ''}
                            </div>
                            <div class="col-md-6 ps-md-4 d-flex flex-column gap-2 justify-content-center">
                                <button class="btn btn-outline-primary btn-sm text-start" id="btn-qikink-sync" onclick="qikinkSyncOrder(${order.id})">
                                    <i class="fa-solid fa-arrows-rotate me-2"></i> Sync Order & Tracking Status
                                </button>
                                <div class="text-muted small mt-2">
                                    <i class="fa-solid fa-circle-info me-1"></i> Custom POD designs will be printed exactly as uploaded by customer.
                                </div>
                            </div>
                        </div>
                `;
            }

            qikinkHtml += `
                    </div>
                </div>
                <hr>
            `;
        }
        
        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="fw-bold">Order Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Order Number:</strong></td><td>${order.order_number}</td></tr>
                        <tr><td><strong>Order Date:</strong></td><td>${new Date(order.created_at).toLocaleDateString('en-US', {day: '2-digit', month: 'short', year: 'numeric'})}</td></tr>
                        <tr><td><strong>Order Status:</strong></td><td>${orderStatusBadge}</td></tr>
                        <tr><td><strong>Payment Status:</strong></td><td>${paymentStatusBadge}</td></tr>
                        <tr><td><strong>Payment Method:</strong></td><td>${order.payment_method === 'razorpay' ? 'Razorpay' : 'COD'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Customer Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Name:</strong></td><td>${order.name}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>${order.email}</td></tr>
                        <tr><td><strong>Phone:</strong></td><td>${order.phone}</td></tr>
                        <tr><td><strong>Address:</strong></td><td>${order.address}, ${order.city} - ${order.zip}</td></tr>
                    </table>
                </div>
            </div>
            
            <hr>
            
            ${shiprocketHtml}
            ${qikinkHtml}
            
            <h6 class="fw-bold mb-3">Order Items</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Size</th>
                            <th>Color</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                            <td><strong>₹${parseFloat(order.subtotal).toFixed(2)}</strong></td>
                        </tr>
                        ${order.discount > 0 ? `
                        <tr>
                            <td colspan="6" class="text-end"><strong>Discount:</strong></td>
                            <td><strong>-₹${parseFloat(order.discount).toFixed(2)}</strong></td>
                        </tr>
                        ` : ''}
                        <tr class="table-primary">
                            <td colspan="6" class="text-end"><strong>Total:</strong></td>
                            <td><strong>₹${parseFloat(order.total).toFixed(2)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        
        $('#orderDetailsBody').html(html);
    }

    // ─── Shiprocket Action Implementations ───

    function shiprocketCreateShipment(orderId) {
        let btn = $('#btn-sr-create');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Registering...');

        $.ajax({
            url: "{{ route('admin.shiprocket.create-shipment') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order_id: orderId
            },
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    refreshOrderModal(orderId);
                } else {
                    Swal.fire('Error', res.message || 'Failed to create shipment', 'error');
                    btn.prop('disabled', false).html('<i class="fa-solid fa-cloud-arrow-up me-1"></i> Register Order');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'API server error, please check connection.', 'error');
                btn.prop('disabled', false).html('<i class="fa-solid fa-cloud-arrow-up me-1"></i> Register Order');
            }
        });
    }

    function shiprocketLoadCouriers(orderId) {
        let section = $('#couriers-loading-section');
        section.html('<div class="text-center py-2"><i class="fa-solid fa-spinner fa-spin fa-lg text-secondary"></i><span class="ms-2 small text-muted">Finding delivery options...</span></div>');

        $.ajax({
            url: '/admin/shiprocket/get-couriers/' + orderId,
            type: 'GET',
            success: function(res) {
                if(res.success && res.couriers && res.couriers.length > 0) {
                    let select = $('#sr-courier-select');
                    select.empty();
                    res.couriers.forEach(function(c) {
                        let rate = parseFloat(c.freight_charge).toFixed(2);
                        select.append(`<option value="${c.courier_company_id}">${c.courier_name} (Rate: ₹${rate} | Delivery: ${c.etd || 'N/A'} days)</option>`);
                    });
                    section.hide();
                    $('#couriers-selection-section').show();
                } else {
                    Swal.fire('No Delivery Options', res.message || 'Shiprocket returned no serviceable courier options.', 'warning');
                    section.html(`
                        <button class="btn btn-sm btn-secondary w-100" onclick="shiprocketLoadCouriers(${orderId})">
                            <i class="fa-solid fa-rotate-right me-1"></i> Retry Courier Search
                        </button>
                    `);
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Unable to fetch serviceable couriers.', 'error');
                section.html(`
                    <button class="btn btn-sm btn-secondary w-100" onclick="shiprocketLoadCouriers(${orderId})">
                        <i class="fa-solid fa-rotate-right me-1"></i> Retry Courier Search
                    </button>
                `);
            }
        });
    }

    function shiprocketAssignAwb(orderId) {
        let courierId = $('#sr-courier-select').val();
        if(!courierId) {
            Swal.fire('Warning', 'Please select a courier company.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Confirm Courier Booking',
            text: 'Are you sure you want to book this delivery and generate the shipping request?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, book it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    url: "{{ route('admin.shiprocket.assign-awb') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        order_id: orderId,
                        courier_id: courierId
                    },
                    success: function(res) {
                        if(res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Courier Booked!',
                                text: res.message,
                                timer: 3000,
                                showConfirmButton: true
                            });
                            refreshOrderModal(orderId);
                            recordList();
                        } else {
                            Swal.fire('Booking Failed', res.message || 'Failed to book courier', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Server error booking courier.', 'error');
                    }
                });
            }
        });
    }

    function shiprocketGenerateLabel(orderId) {
        Swal.showLoading();
        $.ajax({
            url: '/admin/shiprocket/label/' + orderId,
            type: 'GET',
            success: function(res) {
                Swal.close();
                if(res.success && res.label_url) {
                    window.open(res.label_url, '_blank');
                } else {
                    Swal.fire('Error', res.message || 'Failed to retrieve label url.', 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Error', 'Server error generating label pdf.', 'error');
            }
        });
    }

    function shiprocketGenerateManifest(orderId) {
        Swal.showLoading();
        $.ajax({
            url: '/admin/shiprocket/manifest/' + orderId,
            type: 'GET',
            success: function(res) {
                Swal.close();
                if(res.success) {
                    if (res.manifest_url) {
                        window.open(res.manifest_url, '_blank');
                    } else {
                        Swal.fire('Manifest Details', JSON.stringify(res.data || res), 'info');
                    }
                } else {
                    Swal.fire('Error', res.message || 'Failed to retrieve manifest details.', 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Error', 'Server error generating manifest.', 'error');
            }
        });
    }

    function shiprocketCancelShipment(orderId) {
        Swal.fire({
            title: 'Cancel Shipment?',
            text: 'Are you sure you want to cancel the active shipment and clear all tracking data? This resets the order status to Pending.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    url: "{{ route('admin.shiprocket.cancel') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        order_id: orderId
                    },
                    success: function(res) {
                        if(res.success) {
                            Swal.fire('Cancelled', res.message, 'success');
                            refreshOrderModal(orderId);
                            recordList();
                        } else {
                            Swal.fire('Error', res.message || 'Failed to cancel shipment.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Server error cancelling shipment.', 'error');
                    }
                });
            }
        });
    }

    function refreshOrderModal(orderId) {
        $.ajax({
            url: '/admin/orders-details/' + orderId,
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    displayOrderDetails(response.data);
                }
            }
        });
    }

    function qikinkCreateOrder(orderId) {
        let btn = $('#btn-qikink-create');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...');

        $.ajax({
            url: "{{ route('admin.qikink.push-order') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order_id: orderId
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-cloud-arrow-up me-1"></i> Send Order to Qikink');
                if(res.success) {
                    Swal.fire('Pushed!', res.message, 'success');
                    refreshOrderModal(orderId);
                    recordList();
                } else {
                    Swal.fire('Push Failed', res.message || 'Failed to send order to Qikink.', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-cloud-arrow-up me-1"></i> Send Order to Qikink');
                let err = xhr.responseJSON ? xhr.responseJSON.message : 'Server error sending order.';
                Swal.fire('Error', err, 'error');
            }
        });
    }

    function qikinkSyncOrder(orderId) {
        let btn = $('#btn-qikink-sync');
        btn.prop('disabled', true).html('<i class="fa-solid fa-arrows-rotate fa-spin me-1"></i> Syncing...');

        $.ajax({
            url: '/admin/qikink/sync-order/' + orderId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-arrows-rotate me-2"></i> Sync Order & Tracking Status');
                if(res.success) {
                    Swal.fire('Synced!', res.message, 'success');
                    refreshOrderModal(orderId);
                } else {
                    Swal.fire('Sync Failed', res.message || 'Failed to sync Qikink status.', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-arrows-rotate me-2"></i> Sync Order & Tracking Status');
                Swal.fire('Error', 'Server error syncing Qikink status.', 'error');
            }
        });
    }
</script>

<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.4"></script>
@endpush
