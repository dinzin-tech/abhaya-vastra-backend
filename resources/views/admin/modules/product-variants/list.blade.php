@extends('admin.layouts.app')

@push('meta')
<title>Product Variants | {{ config('app.name') }}</title>
<meta content="Product Variants" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product Variants</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="row mb-3">
            <div class="col-12 text-end">
                <a class="btn btn-primary" href="{{ route('product-variants.create') }}">Add Product Variant</a>
            </div>
        </div>

        <div class="col-xxl-12">
            <div class="card__wrapper">
                <div class="table__wrapper table-responsive">
                    <table class="table mb-20 multiple_tables" id="record-list" data-url="{{route('product-variants.list')}}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>Sr. No.</th>
                                <th>Product</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Weight</th>
                                <th>Images</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table__body">
                            <!-- AJAX rows will be injected here -->
                        </tbody>
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
@stop

@push('appendJs')
<script src="{{ asset('assets/js/vendor/jquery-3.7.0.js') }}"></script>

<!-- Plugins (order matters!) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('assets/js/vendor/jquery.barrating.js') }}"></script>

<!-- Other vendor scripts -->
<script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/dropzone.js') }}"></script>
<script src="{{ asset('assets/js/plugins/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>

<!-- main.js (after all plugins) -->
<!-- <script src="{{ asset('assets/js/main.js') }}"></script> -->

<!-- Custom inline scripts -->
<script type="text/javascript">
    var searchUrl = "{{route('product-variants.list')}}";
    var listUrl = "{{route('product-variants.index')}}";
    var deleteUrl = "{{route('product-variants.delete')}}";
    var tblObj = $("#record-list");

    $(document).ready(function() {
        // Initialize plugins safely
        if($('.select2').length){
            $('.select2').select2();
        }
        if($('.datepicker').length){
            $('.datepicker').flatpickr({ dateFormat: 'Y-m-d' });
        }
        if($('.rating').length){
            $('.rating').barrating({ theme: 'fontawesome-stars' });
        }

        // Toastr session messages
        @if(session('success'))
            toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
            toastr.error("{{ session('error') }}");
        @endif

        // Load records
        recordList();
    });
</script>

<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.4"></script>
@endpush
