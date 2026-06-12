@extends('admin.layouts.app')
@push('meta')
<title>Returns | {{ config('app.name') }}</title>
<meta content="Returns" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Returns</li>
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
                    <table class="table mb-20 multiple_tables" id="record-list" data-url="{{route('returns.list')}}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>Sr. No.</th>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Reason</th>
                                <th>Status</th>
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

<!-- Return Details Modal -->
<div class="modal fade" id="returnDetailsModal" tabindex="-1" aria-labelledby="returnDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnDetailsModalLabel">Return Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="returnDetailsBody">
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
    var searchUrl = "{{ route('returns.list') }}";
    var listUrl = "{{ route('returns.index') }}";
    var deleteUrl = "{{ route('returns.delete') }}";
    var tblObj = $("#record-list");

    $(document).ready(function() {
        recordList();
    });

    // View Return Details in Modal
    function viewReturnDetails(returnId) {
        // Show modal using Bootstrap 5
        var myModal = new bootstrap.Modal(document.getElementById('returnDetailsModal'));
        myModal.show();

        // Show loading spinner
        $('#returnDetailsBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        // Fetch return details
        $.ajax({
            url: '/admin/returns-details/' + returnId,
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    displayReturnDetails(response.data);
                } else {
                    $('#returnDetailsBody').html('<div class="alert alert-danger">Failed to load return details</div>');
                }
            },
            error: function(xhr) {
                $('#returnDetailsBody').html('<div class="alert alert-danger">Error loading return details</div>');
            }
        });
    }

    function displayReturnDetails(returnData) {
        let statusBadge = '';
        switch(returnData.status) {
            case 'pending':
                statusBadge = '<span class="badge bg-warning">Pending</span>';
                break;
            case 'approved':
                statusBadge = '<span class="badge bg-success">Approved</span>';
                break;
            case 'rejected':
                statusBadge = '<span class="badge bg-danger">Rejected</span>';
                break;
            case 'completed':
                statusBadge = '<span class="badge bg-info">Completed</span>';
                break;
            default:
                statusBadge = '<span class="badge bg-secondary">' + returnData.status + '</span>';
        }

        let imagesHtml = '';
        if(returnData.images && returnData.images.length > 0) {
            returnData.images.forEach(function(image, index) {
                imagesHtml += `
                    <div class="col-md-3 mb-3">
                        <div class="return-image-container">
                            <img src="${image}" alt="Return Image ${index + 1}" class="img-fluid img-thumbnail">
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
                    <h6 class="fw-bold">Return Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Return ID:</strong></td><td>#${returnData.id}</td></tr>
                        <tr><td><strong>Order Number:</strong></td><td><a href="/admin/orders-details/${returnData.order_id}" class="text-primary">#${returnData.order.order_number}</a></td></tr>
                        <tr><td><strong>Status:</strong></td><td>${statusBadge}</td></tr>
                        <tr><td><strong>Submitted Date:</strong></td><td>${new Date(returnData.created_at).toLocaleDateString('en-US', {day: '2-digit', month: 'short', year: 'numeric'})}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Customer Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Name:</strong></td><td>${returnData.user.name}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>${returnData.user.email}</td></tr>
                    </table>
                </div>
            </div>

            <hr>

            <h6 class="fw-bold mb-3">Return Details</h6>
            <div class="mb-3">
                <strong>Reason:</strong>
                <p class="border p-3 bg-light">${returnData.reason}</p>
            </div>

            ${returnData.tracking_id ? `
                <div class="mb-3">
                    <strong>Tracking ID:</strong>
                    <p>${returnData.tracking_id}</p>
                </div>
            ` : ''}

            ${returnData.admin_note ? `
                <div class="mb-3">
                    <strong>Admin Note:</strong>
                    <p class="border p-3 bg-warning">${returnData.admin_note}</p>
                </div>
            ` : ''}

            <h6 class="fw-bold mb-3">Uploaded Images</h6>
            <div class="row">
                ${imagesHtml}
            </div>
        `;

        $('#returnDetailsBody').html(html);
    }
</script>

<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.4"></script>
@endpush
