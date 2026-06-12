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
            itemsHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.name}</td>
                    <td>${item.price ? '$' + parseFloat(item.price).toFixed(2) : 'N/A'}</td>
                    <td>${item.quantity || 1}</td>
                    <td>${item.selectedSize || 'N/A'}</td>
                    <td>${item.selectedColor || 'N/A'}</td>
                    <td><strong>$${(item.price * item.quantity).toFixed(2)}</strong></td>
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
                            <td><strong>$${parseFloat(order.subtotal).toFixed(2)}</strong></td>
                        </tr>
                        ${order.discount > 0 ? `
                        <tr>
                            <td colspan="6" class="text-end"><strong>Discount:</strong></td>
                            <td><strong>-$${parseFloat(order.discount).toFixed(2)}</strong></td>
                        </tr>
                        ` : ''}
                        <tr class="table-primary">
                            <td colspan="6" class="text-end"><strong>Total:</strong></td>
                            <td><strong>$${parseFloat(order.total).toFixed(2)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        
        $('#orderDetailsBody').html(html);
    }
</script>

<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.4"></script>
@endpush
