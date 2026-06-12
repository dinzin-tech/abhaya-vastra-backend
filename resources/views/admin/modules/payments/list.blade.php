@extends('admin.layouts.app')
@push('meta')
<title>Payments | {{ config('app.name') }}</title>
<meta content="Payments" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payments</li>
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
                                <input type="text" class="form-control" id="razorpay_payment_id" name="razorpay_payment_id"
                                    value="{{ $razorpay_payment_id ? $razorpay_payment_id : '' }}" placeholder="Payment ID">
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
                                <input type="text" class="form-control" id="amount" name="amount"
                                    value="{{ $amount ? $amount : '' }}" placeholder="Amount">
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
                    <table class="table mb-20 multiple_tables" id="record-list" data-url="{{route('payments.list')}}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>Sr. No.</th>
                                <th>Payment Order Id</th>
                                <th>Payment Id</th>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table__body"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="text-right"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
    var searchUrl = "{{ route('payments.list') }}";
    var listUrl = "{{ route('payments.index') }}";
    var deleteUrl = "{{ route('payments.delete') }}";
    var tblObj = $("#record-list");

    $(document).ready(function() {
        recordList();
    });
</script>

<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.4"></script>
@endpush
