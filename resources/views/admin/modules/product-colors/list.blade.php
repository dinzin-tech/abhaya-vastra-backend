@extends('admin.layouts.app')

@push('meta')
<title>Product Colors | {{ config('app.name') }}</title>
<meta content="Product Colors" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@push('appendCss')
<style>
    .pcl-header {
        display:flex; align-items:center; justify-content:space-between;
        flex-wrap:wrap; gap:12px; margin-bottom:20px;
    }
    .pcl-header h4 { font-size:1.25rem; font-weight:700; color:#1e293b; margin:0; }

    .pcl-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .btn-add-color {
        display:inline-flex; align-items:center; gap:7px;
        background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff;
        border:none; border-radius:9px; padding:10px 20px; font-size:0.85rem;
        font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(99,102,241,.3);
        transition: transform .15s, box-shadow .15s;
    }
    .btn-add-color:hover { color:#fff; transform:translateY(-1px); box-shadow:0 6px 18px rgba(99,102,241,.4); }

    /* Filter bar */
    .pcl-filters {
        display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;
        align-items:center;
    }
    .pcl-filter-search {
        display:flex; align-items:center; background:#fff; border:1.5px solid #e2e8f0;
        border-radius:9px; padding:0 14px; gap:8px; flex:1; min-width:200px; max-width:320px;
    }
    .pcl-filter-search input {
        border:none; outline:none; padding:9px 0; font-size:.875rem;
        color:#1e293b; background:transparent; width:100%;
    }
    .pcl-filter-search i { color:#94a3b8; }
    .btn-filter-clear {
        background:transparent; border:none; color:#94a3b8; font-size:.82rem;
        cursor:pointer; padding:6px 10px; border-radius:7px; transition: color .2s;
    }
    .btn-filter-clear:hover { color:#ef4444; }

    /* Stats cards */
    .pcl-stats { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-bottom:20px; }
    @media(max-width:500px){ .pcl-stats { grid-template-columns:1fr; } }
    .pcl-stat-card {
        background:#fff; border:1.5px solid #f1f5f9; border-radius:12px;
        padding:16px 18px; display:flex; align-items:center; gap:14px;
    }
    .pcl-stat-icon {
        width:44px; height:44px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        font-size:1.2rem; flex-shrink:0;
    }
    .pcl-stat-label { font-size:0.72rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; }
    .pcl-stat-value { font-size:1.4rem; font-weight:800; color:#1e293b; line-height:1.1; }

    /* Thumbnail and swatches */
    .color-swatch-circle {
        width:24px; height:24px; border-radius:50%;
        border:1.5px solid #cbd5e1; display:inline-block;
        vertical-align:middle; margin-right:8px;
    }
    .color-image-thumb {
        width:44px; height:44px; border-radius:6px; object-fit:cover;
        border:1.5px solid #e2e8f0; margin-right:4px;
    }

    .table__icon { border-radius:7px; padding:6px 10px; }
    .table__icon.edit { background:#eef2ff; color:#6366f1; border:none; }
    .table__icon.delete { background:#fee2e2; color:#ef4444; border:none; cursor:pointer; }
</style>
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-20">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product Colors</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Header --}}
    <div class="pcl-header">
        <h4><i class="fa-regular fa-palette me-2" style="color:#6366f1"></i> Product Colors</h4>
        <div class="pcl-actions">
            <a href="{{ route('product-colors.create') }}" class="btn-add-color">
                <i class="fa-regular fa-plus"></i> Add Product Color
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="pcl-stats">
        <div class="pcl-stat-card">
            <div class="pcl-stat-icon" style="background:#eef2ff;">🎨</div>
            <div>
                <div class="pcl-stat-label">Total Product Colors</div>
                <div class="pcl-stat-value" id="statTotal">—</div>
            </div>
        </div>
        <div class="pcl-stat-card">
            <div class="pcl-stat-icon" style="background:#f0fdf4;">📦</div>
            <div>
                <div class="pcl-stat-label">Assigned Products</div>
                <div class="pcl-stat-value" id="statProducts">—</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="pcl-filters">
        <div class="pcl-filter-search">
            <i class="fa-regular fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search color or product name…" oninput="applyFilters()">
        </div>
        <button class="btn-filter-clear" onclick="clearFilters()" title="Clear filters">
            <i class="fa-regular fa-xmark"></i> Clear
        </button>
    </div>

    <div class="row">
        <div class="col-xxl-12">
            <div class="card__wrapper">
                <div class="table__wrapper table-responsive">
                    <table class="table mb-20 multiple_tables" id="record-list"
                        data-url="{{ route('product-colors.list') }}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>#</th>
                                <th>Product</th>
                                <th>Color</th>
                                <th>Images Preview</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table__body">
                            <!-- AJAX rows will be injected here -->
                        </tbody>
                        <tfoot>
                            <tr><td colspan="5" class="text-right"></td></tr>
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

<script type="text/javascript">
    var searchUrl  = "{{ route('product-colors.list') }}";
    var listUrl    = "{{ route('product-colors.index') }}";
    var deleteUrl  = "{{ route('product-colors.delete') }}";
    var tblObj     = $("#record-list");

    function applyFilters() {
        var q = $('#searchInput').val();
        recordList(q);
    }

    function clearFilters() {
        $('#searchInput').val('');
        recordList();
    }

    function recordList(q) {
        q = q || $('#searchInput').val() || '';

        $.ajax({
            url: searchUrl,
            data: { q: q },
            success: function(data) {
                $('.table__body').html(data.rows);
                if (data.pagination) {
                    $('tfoot td').html(data.pagination);
                }
                if (data.stats) {
                    $('#statTotal').text(data.stats.total || 0);
                    $('#statProducts').text(data.stats.unique_products || 0);
                }
            }
        });
    }

    $(document).ready(function() {
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        recordList();
    });
</script>
<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}"></script>
@endpush
