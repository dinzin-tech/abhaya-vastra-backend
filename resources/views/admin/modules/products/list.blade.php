@extends('admin.layouts.app')
@push('meta')
<title>Products | {{ config('app.name') }}</title>
<meta content="Products" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@push('appendCss')
<style>
    .pl-header {
        display:flex; align-items:center; justify-content:space-between;
        flex-wrap:wrap; gap:12px; margin-bottom:20px;
    }
    .pl-header h4 { font-size:1.25rem; font-weight:700; color:#1e293b; margin:0; }

    .pl-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .btn-bulk {
        display:inline-flex; align-items:center; gap:7px;
        background:#f0fdf4; color:#16a34a; border:1.5px solid #86efac;
        border-radius:9px; padding:9px 18px; font-size:0.85rem; font-weight:700;
        text-decoration:none; transition: background .2s;
    }
    .btn-bulk:hover { background:#dcfce7; color:#15803d; }
    .btn-add-product {
        display:inline-flex; align-items:center; gap:7px;
        background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff;
        border:none; border-radius:9px; padding:10px 20px; font-size:0.85rem;
        font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(99,102,241,.3);
        transition: transform .15s, box-shadow .15s;
    }
    .btn-add-product:hover { color:#fff; transform:translateY(-1px); box-shadow:0 6px 18px rgba(99,102,241,.4); }

    /* Filter bar */
    .pl-filters {
        display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;
        align-items:center;
    }
    .pl-filter-search {
        display:flex; align-items:center; background:#fff; border:1.5px solid #e2e8f0;
        border-radius:9px; padding:0 14px; gap:8px; flex:1; min-width:200px; max-width:320px;
    }
    .pl-filter-search input {
        border:none; outline:none; padding:9px 0; font-size:.875rem;
        color:#1e293b; background:transparent; width:100%;
    }
    .pl-filter-search i { color:#94a3b8; }
    .pl-filter-select {
        padding:9px 14px; border:1.5px solid #e2e8f0; border-radius:9px;
        font-size:.875rem; color:#374151; background:#fff; outline:none;
        cursor:pointer; transition: border-color .2s;
    }
    .pl-filter-select:focus { border-color:#6366f1; }
    .btn-filter-clear {
        background:transparent; border:none; color:#94a3b8; font-size:.82rem;
        cursor:pointer; padding:6px 10px; border-radius:7px; transition: color .2s;
    }
    .btn-filter-clear:hover { color:#ef4444; }

    /* Stats cards */
    .pl-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
    @media(max-width:900px){ .pl-stats { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:500px){ .pl-stats { grid-template-columns:1fr; } }
    .pl-stat-card {
        background:#fff; border:1.5px solid #f1f5f9; border-radius:12px;
        padding:16px 18px; display:flex; align-items:center; gap:14px;
    }
    .pl-stat-icon {
        width:44px; height:44px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        font-size:1.2rem; flex-shrink:0;
    }
    .pl-stat-label { font-size:0.72rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; }
    .pl-stat-value { font-size:1.4rem; font-weight:800; color:#1e293b; line-height:1.1; }

    /* Table enhancements */
    .product-thumb {
        width:48px; height:48px; border-radius:8px; object-fit:cover;
        border:1.5px solid #e2e8f0;
    }
    .product-thumb-placeholder {
        width:48px; height:48px; border-radius:8px;
        background:#f1f5f9; display:flex; align-items:center; justify-content:center;
        color:#94a3b8; font-size:1.2rem; border:1.5px solid #e2e8f0;
    }
    .badge-gender {
        display:inline-block; padding:3px 9px; border-radius:999px;
        font-size:0.7rem; font-weight:700; text-transform:capitalize; letter-spacing:.3px;
    }
    .badge-male   { background:#dbeafe; color:#1d4ed8; }
    .badge-female { background:#fce7f3; color:#be185d; }
    .badge-unisex { background:#fef3c7; color:#b45309; }
    .badge-flag {
        display:inline-flex; align-items:center; gap:4px;
        padding:2px 8px; border-radius:999px; font-size:0.68rem; font-weight:700;
    }
    .badge-bestseller { background:#fef9c3; color:#a16207; }
    .badge-featured   { background:#fee2e2; color:#b91c1c; }

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
                    <li class="breadcrumb-item active" aria-current="page">Products</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Header --}}
    <div class="pl-header">
        <h4><i class="fa-regular fa-boxes-stacked me-2" style="color:#6366f1"></i> Product Catalog</h4>
        <div class="pl-actions">
            <a href="{{ route('products.bulk-import') }}" class="btn-bulk">
                <i class="fa-regular fa-file-arrow-up"></i> Bulk Import
            </a>
            <a href="{{ route('products.create') }}" class="btn-add-product">
                <i class="fa-regular fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="pl-stats" id="productStats">
        <div class="pl-stat-card">
            <div class="pl-stat-icon" style="background:#eef2ff;">📦</div>
            <div>
                <div class="pl-stat-label">Total Products</div>
                <div class="pl-stat-value" id="statTotal">—</div>
            </div>
        </div>
        <div class="pl-stat-card">
            <div class="pl-stat-icon" style="background:#fef9c3;">⭐</div>
            <div>
                <div class="pl-stat-label">Best Sellers</div>
                <div class="pl-stat-value" id="statBest">—</div>
            </div>
        </div>
        <div class="pl-stat-card">
            <div class="pl-stat-icon" style="background:#fee2e2;">🔥</div>
            <div>
                <div class="pl-stat-label">Featured</div>
                <div class="pl-stat-value" id="statFeatured">—</div>
            </div>
        </div>
        <div class="pl-stat-card">
            <div class="pl-stat-icon" style="background:#f0fdf4;">🗂️</div>
            <div>
                <div class="pl-stat-label">Categories</div>
                <div class="pl-stat-value" id="statCats">—</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="pl-filters">
        <div class="pl-filter-search">
            <i class="fa-regular fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search products…" oninput="applyFilters()">
        </div>
        <select class="pl-filter-select" id="filterGender" onchange="applyFilters()">
            <option value="">All Genders</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="unisex">Unisex</option>
        </select>
        <select class="pl-filter-select" id="filterFlag" onchange="applyFilters()">
            <option value="">All Products</option>
            <option value="best_seller">Best Sellers</option>
            <option value="featured">Featured</option>
        </select>
        <button class="btn-filter-clear" onclick="clearFilters()" title="Clear filters">
            <i class="fa-regular fa-xmark"></i> Clear
        </button>
    </div>

    <div class="row">
        <div class="col-xxl-12">
            <div class="card__wrapper">
                <div class="table__wrapper table-responsive">
                    <table class="table mb-20 multiple_tables" id="record-list"
                        data-url="{{ route('products.list') }}">
                        <thead>
                            <tr class="table__title table__sort">
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Gender</th>
                                <th>Flags</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table__body"></tbody>
                        <tfoot>
                            <tr><td colspan="7" class="text-right"></td></tr>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('assets/js/vendor/jquery.barrating.js') }}"></script>
<script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>

<script type="text/javascript">
    var searchUrl  = "{{ route('products.list') }}";
    var listUrl    = "{{ route('products.index') }}";
    var deleteUrl  = "{{ route('products.delete') }}";
    var tblObj     = $("#record-list");

    function applyFilters() {
        var q      = $('#searchInput').val();
        var gender = $('#filterGender').val();
        var flag   = $('#filterFlag').val();
        recordList(q, gender, flag);
    }

    function clearFilters() {
        $('#searchInput').val('');
        $('#filterGender').val('');
        $('#filterFlag').val('');
        recordList();
    }

    // Override recordList to pass extra params
    function recordList(q, gender, flag) {
        q = q || $('#searchInput').val() || '';
        gender = gender !== undefined ? gender : ($('#filterGender').val() || '');
        flag   = flag   !== undefined ? flag   : ($('#filterFlag').val()   || '');

        $.ajax({
            url: searchUrl,
            data: { q: q, gender: gender, flag: flag },
            success: function(data) {
                $('.table__body').html(data.rows);
                // Update pagination if exists
                if (data.pagination) {
                    $('tfoot td').html(data.pagination);
                }
                // Update stats
                if (data.stats) {
                    $('#statTotal').text(data.stats.total || 0);
                    $('#statBest').text(data.stats.best_seller || 0);
                    $('#statFeatured').text(data.stats.featured || 0);
                    $('#statCats').text(data.stats.categories || 0);
                }
            }
        });
    }

    $(document).ready(function() {
        @if(session('success'))
            toastr.options = { closeButton:true, progressBar:true, positionClass:"toast-top-right", timeOut:"3000" };
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.options = { closeButton:true, progressBar:true, positionClass:"toast-top-right", timeOut:"3000" };
            toastr.error("{{ session('error') }}");
        @endif

        recordList();
    });
</script>
<script type="text/javascript" src="{{ asset('assets/js/list-records.js') }}?v=1.5"></script>
@endpush