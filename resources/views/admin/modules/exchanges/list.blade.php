@extends('admin.layouts.app')
@push('meta')
<title>Exchanges | {{ config('app.name') }}</title>
<meta content="Exchanges" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Exchanges</li>
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
                    <table class="table mb-20 multiple_tables" id="record-list" data-url="{{route('exchanges.list')}}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>Sr. No.</th>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Exchange Details</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table__body"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-right"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Exchange Details Modal -->
<div class="modal fade" id="exchangeDetailsModal" tabindex="-1" aria-labelledby="exchangeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exchangeDetailsModalLabel">Exchange Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="exchangeDetailsBody">
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
<script src="{{ asset('assets/js/main.js') }}"></script>

<script type="text/javascript">
    var searchUrl = "{{ route('exchanges.list') }}";
    var listUrl = "{{ route('exchanges.index') }}";
    var deleteUrl = "{{ route('exchanges.delete') }}";
    var tblObj = $("#record-list");

    $(document).ready(function() {
        recordList();
    });

    // View Exchange Details in Modal
    function viewExchangeDetails(exchangeId) {
        var myModal = new bootstrap.Modal(document.getElementById('exchangeDetailsModal'));
        myModal.show();

        $('#exchangeDetailsBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        $.ajax({
            url: '{{ route("exchanges.details", ":id") }}'.replace(':id', exchangeId),
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    displayExchangeDetails(response.data);
                } else {
                    $('#exchangeDetailsBody').html('<div class="alert alert-danger">Failed to load exchange details</div>');
                }
            },
            error: function(xhr) {
                $('#exchangeDetailsBody').html('<div class="alert alert-danger">Error loading exchange details</div>');
            }
        });
    }

    function displayExchangeDetails(exchange) {
        let statusBadge = '';
        switch(exchange.status) {
            case 'pending':
                statusBadge = '<span class="badge bg-warning">Pending</span>';
                break;
            case 'approved':
                statusBadge = '<span class="badge bg-success">Approved</span>';
                break;
            case 'rejected':
                statusBadge = '<span class="badge bg-danger">Rejected</span>';
                break;
            case 'pickup_scheduled':
                statusBadge = '<span class="badge bg-info">Pickup Scheduled</span>';
                break;
            case 'picked_up':
                statusBadge = '<span class="badge bg-primary">Picked Up</span>';
                break;
            case 'exchange_shipped':
                statusBadge = '<span class="badge bg-primary">Exchange Shipped</span>';
                break;
            case 'completed':
                statusBadge = '<span class="badge bg-success">Completed</span>';
                break;
            case 'cancelled':
                statusBadge = '<span class="badge bg-secondary">Cancelled</span>';
                break;
            default:
                statusBadge = '<span class="badge bg-secondary">' + exchange.status + '</span>';
        }

        let paymentBadge = '';
        switch(exchange.payment_status) {
            case 'paid':
                paymentBadge = '<span class="badge bg-success">Paid</span>';
                break;
            case 'pending':
                paymentBadge = '<span class="badge bg-warning">Pending</span>';
                break;
            case 'failed':
                paymentBadge = '<span class="badge bg-danger">Failed</span>';
                break;
            default:
                paymentBadge = '<span class="badge bg-secondary">' + exchange.payment_status + '</span>';
        }

        let imagesHtml = '';
        if(exchange.images && exchange.images.length > 0) {
            exchange.images.forEach(function(image, index) {
                imagesHtml += `
                    <div class="col-md-3 mb-3">
                        <div class="exchange-image-container">
                            <img src="${image}" alt="Exchange Image ${index + 1}" class="img-fluid img-thumbnail" style="max-height: 200px; object-fit: cover;">
                        </div>
                    </div>
                `;
            });
        } else {
            imagesHtml = '<p class="text-muted">No images uploaded</p>';
        }

        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="fw-bold">Exchange Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Exchange ID:</strong></td><td>#${exchange.id}</td></tr>
                        <tr><td><strong>Order Number:</strong></td><td><a  class="text-primary">#${exchange.order.order_number}</a></td></tr>
                        <tr><td><strong>Status:</strong></td><td>${statusBadge}</td></tr>
                        <tr><td><strong>Payment Status:</strong></td><td>${paymentBadge}</td></tr>
                        <tr><td><strong>Exchange Charge:</strong></td><td>₹${exchange.exchange_charge}</td></tr>
                        <tr><td><strong>Submitted Date:</strong></td><td>${new Date(exchange.created_at).toLocaleDateString('en-US', {day: '2-digit', month: 'short', year: 'numeric'})}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Customer Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Name:</strong></td><td>${exchange.order.name}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>${exchange.order.email}</td></tr>
                        <tr><td><strong>Phone:</strong></td><td>${exchange.order.phone}</td></tr>
                        <tr><td><strong>Address:</strong></td><td>${exchange.order.address}, ${exchange.order.city}, ${exchange.order.state} - ${exchange.order.zip}</td></tr>
                    </table>
                </div>
            </div>

            <hr>

            <h6 class="fw-bold mb-3">Exchange Details</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-danger">Original Product</h6>
                            <p class="mb-1"><strong>Size:</strong> ${exchange.original_size}</p>
                            ${exchange.original_color ? `<p class="mb-0"><strong>Color:</strong> ${exchange.original_color}</p>` : ''}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-success">Exchange To</h6>
                            <p class="mb-1"><strong>Size:</strong> ${exchange.exchange_size}</p>
                            ${exchange.exchange_color ? `<p class="mb-0"><strong>Color:</strong> ${exchange.exchange_color}</p>` : ''}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <strong>Reason:</strong>
                <p class="border p-3 bg-light">${exchange.reason}</p>
            </div>

            ${exchange.shiprocket_pickup_awb_code ? `
                <div class="mb-3">
                    <h6 class="fw-bold">Pickup Details</h6>
                    <p><strong>AWB Code:</strong> ${exchange.shiprocket_pickup_awb_code}</p>
                    <p><strong>Courier:</strong> ${exchange.shiprocket_pickup_courier_name || 'N/A'}</p>
                </div>
            ` : ''}

            ${exchange.shiprocket_delivery_awb_code ? `
                <div class="mb-3">
                    <h6 class="fw-bold">Delivery Details</h6>
                    <p><strong>AWB Code:</strong> ${exchange.shiprocket_delivery_awb_code}</p>
                    <p><strong>Courier:</strong> ${exchange.shiprocket_delivery_courier_name || 'N/A'}</p>
                </div>
            ` : ''}

            ${exchange.admin_note ? `
                <div class="mb-3">
                    <strong>Admin Note:</strong>
                    <p class="border p-3 bg-warning">${exchange.admin_note}</p>
                </div>
            ` : ''}

            <h6 class="fw-bold mb-3">Uploaded Images</h6>
            <div class="row">
                ${imagesHtml}
            </div>
        `;

        $('#exchangeDetailsBody').html(html);
    }
</script>

<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.4"></script>
@endpush
