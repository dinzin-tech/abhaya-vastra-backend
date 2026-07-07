@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Product | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Product" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@push('appendCss')
<style>
    /* ============================================================
       PRODUCT FORM — PREMIUM UI
    ============================================================ */
    .pf-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .pf-header h4 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .pf-header .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.875rem;
        padding: 8px 18px;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        color: #475569;
        text-decoration: none;
        transition: all .2s;
    }
    .pf-header .btn-back:hover { background:#f1f5f9; color:#0f172a; }

    /* Tabs */
    .pf-tabs { display:flex; gap:4px; border-bottom:2px solid #e2e8f0; margin-bottom:28px; }
    .pf-tab-btn {
        display:inline-flex; align-items:center; gap:8px;
        padding:10px 20px; border:none; background:transparent;
        font-size:0.875rem; font-weight:600; color:#64748b;
        border-bottom:2px solid transparent; margin-bottom:-2px;
        cursor:pointer; border-radius:6px 6px 0 0;
        transition: color .2s, border-color .2s;
    }
    .pf-tab-btn.active { color:#6366f1; border-bottom-color:#6366f1; background:#eef2ff; }
    .pf-tab-btn .tab-icon { font-size:1rem; }
    .pf-tab-panel { display:none; }
    .pf-tab-panel.active { display:block; }

    /* Form rows */
    .pf-row { display:grid; gap:20px; margin-bottom:20px; }
    .pf-row-1 { grid-template-columns: 1fr; }
    .pf-row-2 { grid-template-columns: 1fr 1fr; }
    .pf-row-3 { grid-template-columns: 1fr 1fr 1fr; }
    @media(max-width:768px) {
        .pf-row-2, .pf-row-3 { grid-template-columns:1fr; }
    }

    .pf-field label {
        display:block; font-size:0.8rem; font-weight:600;
        color:#374151; margin-bottom:6px; letter-spacing:.3px;
    }
    .pf-field label .req { color:#ef4444; margin-left:2px; }
    .pf-field .form-control, .pf-field select, .pf-field textarea {
        width:100%; padding:10px 14px; font-size:0.9rem;
        border:1.5px solid #e2e8f0; border-radius:8px;
        background:#f8fafc; color:#1e293b; transition: border-color .2s, box-shadow .2s;
        outline:none;
    }
    .pf-field .form-control:focus, .pf-field select:focus, .pf-field textarea:focus {
        border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1);
        background:#fff;
    }
    .pf-field textarea { resize:vertical; min-height:110px; }

    /* Slug badge */
    .slug-preview {
        font-size:0.78rem; color:#64748b; background:#f1f5f9;
        border:1px dashed #cbd5e1; border-radius:6px; padding:8px 12px;
        margin-top:6px; font-family:monospace; word-break:break-all;
    }

    /* Toggle switches */
    .pf-toggles { display:flex; gap:24px; flex-wrap:wrap; margin-bottom:20px; }
    .pf-toggle { display:flex; align-items:center; gap:10px; cursor:pointer; }
    .pf-toggle input[type=checkbox] { display:none; }
    .pf-toggle-track {
        width:44px; height:24px; background:#e2e8f0; border-radius:999px;
        position:relative; transition: background .2s;
    }
    .pf-toggle-track::after {
        content:''; position:absolute; top:3px; left:3px;
        width:18px; height:18px; background:#fff; border-radius:50%;
        transition: transform .2s; box-shadow:0 1px 4px rgba(0,0,0,.2);
    }
    .pf-toggle input:checked + .pf-toggle-track { background:#6366f1; }
    .pf-toggle input:checked + .pf-toggle-track::after { transform:translateX(20px); }
    .pf-toggle-label { font-size:0.875rem; font-weight:600; color:#374151; user-select:none; }

    /* Dropzone */
    .dropzone-area {
        border:2.5px dashed #c7d2fe; border-radius:12px;
        background:#f5f7ff; padding:28px 20px; text-align:center;
        cursor:pointer; transition: border-color .2s, background .2s;
        position:relative;
    }
    .dropzone-area:hover, .dropzone-area.drag-over {
        border-color:#6366f1; background:#eef2ff;
    }
    .dropzone-area input[type=file] {
        position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;
    }
    .dropzone-icon { font-size:2.5rem; color:#a5b4fc; margin-bottom:10px; }
    .dropzone-title { font-weight:700; font-size:0.95rem; color:#3730a3; }
    .dropzone-sub { font-size:0.78rem; color:#6366f1; margin-top:4px; }
    .dropzone-note { font-size:0.72rem; color:#94a3b8; margin-top:8px; }

    /* Image preview card */
    .img-preview-grid { display:flex; flex-wrap:wrap; gap:12px; margin-top:14px; }
    .img-preview-card {
        position:relative; width:130px; height:130px;
        border-radius:10px; overflow:hidden;
        border:1.5px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,.07);
    }
    .img-preview-card img { width:100%; height:100%; object-fit:cover; display:block; }
    .img-preview-card .img-size-badge {
        position:absolute; bottom:0; left:0; right:0;
        background:rgba(0,0,0,.55); color:#fff; font-size:0.65rem;
        text-align:center; padding:3px 0; font-weight:600;
    }
    .img-preview-card .img-size-badge.warning { background:rgba(239,68,68,.75); }
    .img-preview-card .img-remove-btn {
        position:absolute; top:4px; right:4px; width:22px; height:22px;
        background:#ef4444; color:#fff; border:none; border-radius:50%;
        font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center;
        transition: background .2s;
    }
    .img-preview-card .img-remove-btn:hover { background:#dc2626; }
    .compression-bar {
        height:4px; border-radius:999px; background:#e2e8f0; margin-top:8px; overflow:hidden;
    }
    .compression-bar-fill { height:100%; background:#6366f1; width:0%; transition:width .4s; border-radius:999px; }

    /* Color sections */
    .color-block {
        border:1.5px solid #e2e8f0; border-radius:12px;
        padding:20px; margin-bottom:16px; background:#fafafa;
        transition: border-color .2s;
    }
    .color-block:hover { border-color:#a5b4fc; }
    .color-block-header {
        display:flex; align-items:center; justify-content:space-between;
        margin-bottom:16px;
    }
    .color-block-header .color-swatch {
        width:30px; height:30px; border-radius:50%;
        border:2px solid #e2e8f0; flex-shrink:0;
    }
    .color-block-title { font-weight:700; font-size:0.9rem; color:#1e293b; }
    .btn-remove-color {
        border:none; background:#fee2e2; color:#ef4444;
        border-radius:8px; padding:6px 14px; font-size:0.78rem;
        font-weight:600; cursor:pointer; transition: background .2s;
    }
    .btn-remove-color:hover { background:#fecaca; }

    /* Variant table */
    .variant-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
    .variant-table th {
        background:#f1f5f9; color:#475569; font-weight:700;
        padding:9px 12px; text-align:left; border-bottom:2px solid #e2e8f0;
    }
    .variant-table td { padding:8px 10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .variant-table input {
        width:100%; padding:6px 10px; border:1.5px solid #e2e8f0;
        border-radius:6px; font-size:0.82rem; background:#fff;
    }
    .variant-table input:focus { outline:none; border-color:#6366f1; }
    .btn-add-variant {
        background:#eef2ff; color:#6366f1; border:none; border-radius:8px;
        padding:7px 16px; font-size:0.8rem; font-weight:700;
        cursor:pointer; margin-top:10px; transition: background .2s;
    }
    .btn-add-variant:hover { background:#e0e7ff; }
    .btn-remove-variant {
        background:#fee2e2; color:#ef4444; border:none;
        border-radius:6px; padding:5px 10px; font-size:0.78rem;
        cursor:pointer;
    }

    /* Add Color button */
    .btn-add-color {
        display:inline-flex; align-items:center; gap:8px;
        background:#6366f1; color:#fff; border:none; border-radius:10px;
        padding:11px 22px; font-size:0.875rem; font-weight:700;
        cursor:pointer; transition: background .2s; margin-top:6px;
    }
    .btn-add-color:hover { background:#4f46e5; }

    /* Action bar */
    .pf-action-bar {
        display:flex; gap:12px; justify-content:flex-end;
        padding-top:24px; border-top:2px solid #f1f5f9; margin-top:8px;
        flex-wrap:wrap;
    }
    .btn-pf-save {
        display:inline-flex; align-items:center; gap:8px;
        background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff;
        border:none; border-radius:10px; padding:12px 28px;
        font-size:0.9rem; font-weight:700; cursor:pointer;
        box-shadow:0 4px 14px rgba(99,102,241,.35); transition: transform .15s, box-shadow .15s;
    }
    .btn-pf-save:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(99,102,241,.4); }
    .btn-pf-edit {
        display:inline-flex; align-items:center; gap:8px;
        background:#fff; color:#6366f1; border:2px solid #6366f1;
        border-radius:10px; padding:10px 24px; font-size:0.9rem;
        font-weight:700; cursor:pointer; transition: background .2s;
    }
    .btn-pf-edit:hover { background:#eef2ff; }

    /* Step nav for tabs */
    .tab-step {
        display:inline-flex; align-items:center; justify-content:center;
        width:22px; height:22px; border-radius:50%; font-size:0.7rem;
        font-weight:800; background:#e2e8f0; color:#64748b; flex-shrink:0;
    }
    .pf-tab-btn.active .tab-step { background:#6366f1; color:#fff; }

    .section-divider {
        border:none; border-top:2px solid #f1f5f9; margin:24px 0;
    }
    .section-heading {
        font-size:0.8rem; font-weight:700; text-transform:uppercase;
        letter-spacing:.7px; color:#94a3b8; margin-bottom:14px;
    }
    .readonly-lock { opacity:.6; pointer-events:none; }

    /* Compress status */
    .compress-status { font-size:0.75rem; color:#6366f1; margin-top:6px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="pf-header">
        <div>
            <h4>
                <i class="fa-regular fa-box-open me-2" style="color:#6366f1"></i>
                {{ $item ? 'Update Product' : 'Add New Product' }}
            </h4>
            <p style="color:#94a3b8;font-size:.8rem;margin:4px 0 0">{{ $item ? 'Edit the product details below and save changes.' : 'Fill in the product details to add to your catalog.' }}</p>
        </div>
        <a href="javascript:history.back()" class="btn-back">
            <i class="fa-regular fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="card__wrapper" style="border-radius:16px;padding:28px;">

        {{-- EDIT mode lock notice --}}
        @if($item)
        <div class="alert d-flex align-items-center gap-2 mb-4" style="background:#fef3c7;border:1.5px solid #fcd34d;border-radius:10px;font-size:.85rem;color:#92400e;padding:12px 18px;">
            <i class="fa-solid fa-lock"></i>
            <span>Form is in <strong>view mode</strong>. Click <strong>Enable Edit</strong> to make changes.</span>
            <button type="button" id="editBtn" class="btn-pf-edit ms-auto" onclick="enableEdit()">
                <i class="fa-regular fa-pen"></i> Enable Edit
            </button>
        </div>
        @endif

        {{-- TABS --}}
        <div class="pf-tabs">
            <button type="button" class="pf-tab-btn active" data-tab="tab-basic">
                <span class="tab-step">1</span>
                <i class="fa-regular fa-circle-info tab-icon"></i> Basic Info
            </button>
            <button type="button" class="pf-tab-btn" data-tab="tab-images">
                <span class="tab-step">2</span>
                <i class="fa-regular fa-images tab-icon"></i> Images
            </button>
            <button type="button" class="pf-tab-btn" data-tab="tab-colors">
                <span class="tab-step">3</span>
                <i class="fa-regular fa-palette tab-icon"></i> Colors & Variants
            </button>
        </div>

        <form id="productForm" action="{{ route('products.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $item->id ?? '' }}">

            {{-- ==========================================
                 TAB 1: BASIC INFO
            ========================================== --}}
            <div class="pf-tab-panel active" id="tab-basic">
                <p class="section-heading">Product Identity</p>

                <div class="pf-row pf-row-2">
                    <div class="pf-field">
                        <label for="name">Product Name <span class="req">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ $item->name ?? '' }}"
                            placeholder="e.g. Silver Bracelet for Women"
                            {{ $item ? 'readonly' : '' }} required />
                    </div>
                    <div class="pf-field">
                        <label for="slug">URL Slug <span class="req">*</span></label>
                        <input type="text" id="slug" name="slug" class="form-control"
                            value="{{ $item->slug ?? '' }}" readonly required
                            placeholder="auto-generated" />
                        <div class="slug-preview" id="slugPreview">
                            /products/<span id="slugVal">{{ $item->slug ?? 'your-product-slug' }}</span>
                        </div>
                    </div>
                </div>

                <div class="pf-row pf-row-2">
                    <div class="pf-field">
                        <label for="category_id">Category <span class="req">*</span></label>
                        <select name="category_id" id="category_id" {{ $item ? 'disabled' : '' }} required>
                            <option value="">— Select Category —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ optional($item)->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pf-field">
                        <label for="gender">Gender <span class="req">*</span></label>
                        <select id="gender" name="gender" {{ $item ? 'disabled' : '' }} required>
                            <option value="">— Select Gender —</option>
                            <option value="male"   {{ optional($item)->gender == 'male'   ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ optional($item)->gender == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="unisex" {{ optional($item)->gender == 'unisex' ? 'selected' : '' }}>Unisex</option>
                        </select>
                    </div>
                </div>

                <div class="pf-row pf-row-1">
                    <div class="pf-field">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control"
                            placeholder="Write a detailed product description..."
                            {{ $item ? 'readonly' : '' }}>{{ $item->description ?? '' }}</textarea>
                    </div>
                </div>

                <hr class="section-divider">
                <p class="section-heading">Product Flags</p>

                <div class="pf-toggles">
                    <label class="pf-toggle">
                        <input type="checkbox" name="best_seller" id="best_seller" value="1"
                            {{ optional($item)->best_seller ? 'checked' : '' }}
                            {{ $item ? 'disabled' : '' }}>
                        <div class="pf-toggle-track"></div>
                        <span class="pf-toggle-label"><i class="fa-solid fa-star" style="color:#f59e0b"></i> Best Seller</span>
                    </label>
                    <label class="pf-toggle">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                            {{ optional($item)->is_featured ? 'checked' : '' }}
                            {{ $item ? 'disabled' : '' }}>
                        <div class="pf-toggle-track"></div>
                        <span class="pf-toggle-label"><i class="fa-solid fa-fire" style="color:#ef4444"></i> Featured Product</span>
                    </label>
                    <label class="pf-toggle">
                        <input type="checkbox" name="customizable" id="customizable" value="1"
                            {{ optional($item)->customizable ? 'checked' : '' }}
                            {{ $item ? 'disabled' : '' }}>
                        <div class="pf-toggle-track"></div>
                        <span class="pf-toggle-label"><i class="fa-solid fa-wand-magic-sparkles" style="color:#8b5cf6"></i> Customizable</span>
                    </label>
                </div>
            </div>

            {{-- ==========================================
                 TAB 2: IMAGES
            ========================================== --}}
            <div class="pf-tab-panel" id="tab-images">
                <div style="background:#eef2ff;border-radius:10px;padding:14px 18px;margin-bottom:20px;border:1.5px solid #c7d2fe;font-size:.82rem;color:#3730a3;">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    <strong>Auto Compression Active</strong> — Images are automatically compressed to under 300KB in your browser before upload. Quality is preserved while keeping files small.
                </div>

                <div class="pf-row pf-row-2">
                    {{-- Main Image --}}
                    <div class="pf-field">
                        <label>Main Image <span class="req">{{ $item ? '' : '*' }}</span></label>
                        <div class="dropzone-area" id="mainImageZone">
                            <input type="file" id="main_image_input" accept="image/*">
                            <div class="dropzone-icon">🖼️</div>
                            <div class="dropzone-title">Drag & Drop or Click</div>
                            <div class="dropzone-sub">Main product photo</div>
                            <div class="dropzone-note">JPG, PNG, WEBP — will be compressed to ≤300KB</div>
                        </div>
                        <div class="compression-bar" id="mainBar" style="display:none">
                            <div class="compression-bar-fill" id="mainBarFill"></div>
                        </div>
                        <div class="compress-status" id="mainStatus"></div>
                        <div class="img-preview-grid" id="mainPreviewGrid">
                            @if($item && $item->main_image)
                            <div class="img-preview-card">
                                <img src="{{ asset('storage/'.$item->main_image) }}" alt="Main">
                                <span class="img-size-badge">Current</span>
                            </div>
                            @endif
                        </div>
                        {{-- Hidden file input for the compressed image --}}
                        <input type="file" name="main_image" id="main_image" style="display:none" accept="image/*">
                    </div>

                    {{-- Zoomed Image --}}
                    <div class="pf-field">
                        <label>Zoomed Image <span class="req">{{ $item ? '' : '*' }}</span></label>
                        <div class="dropzone-area" id="zoomedImageZone">
                            <input type="file" id="zoomed_image_input" accept="image/*">
                            <div class="dropzone-icon">🔍</div>
                            <div class="dropzone-title">Drag & Drop or Click</div>
                            <div class="dropzone-sub">High-detail product photo</div>
                            <div class="dropzone-note">JPG, PNG, WEBP — will be compressed to ≤300KB</div>
                        </div>
                        <div class="compression-bar" id="zoomedBar" style="display:none">
                            <div class="compression-bar-fill" id="zoomedBarFill"></div>
                        </div>
                        <div class="compress-status" id="zoomedStatus"></div>
                        <div class="img-preview-grid" id="zoomedPreviewGrid">
                            @if($item && $item->zoomed_image)
                            <div class="img-preview-card">
                                <img src="{{ asset('storage/'.$item->zoomed_image) }}" alt="Zoomed">
                                <span class="img-size-badge">Current</span>
                            </div>
                            @endif
                        </div>
                        <input type="file" name="zoomed_image" id="zoomed_image" style="display:none" accept="image/*">
                    </div>
                </div>
            </div>

            {{-- ==========================================
                 TAB 3: COLORS & VARIANTS
            ========================================== --}}
            <div class="pf-tab-panel" id="tab-colors">
                <div style="background:#f0fdf4;border-radius:10px;padding:14px 18px;margin-bottom:20px;border:1.5px solid #bbf7d0;font-size:.82rem;color:#166534;">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    Add one color entry per product color variant. Upload color-specific photos (auto-compressed). Add size & stock info as variants within each color.
                </div>

                <div id="colorsContainer">
                    @if($item && $item->colors->count())
                        @foreach($item->colors as $colorIndex => $color)
                        <div class="color-block" data-color-index="{{ $colorIndex }}">
                            <div class="color-block-header">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div class="color-swatch" style="background:{{ $color->color }};"></div>
                                    <span class="color-block-title">{{ ucfirst($color->color) }}</span>
                                </div>
                                <button type="button" class="btn-remove-color" onclick="removeColorBlock(this)" {{ $item ? 'disabled' : '' }}>
                                    <i class="fa-regular fa-trash"></i> Remove
                                </button>
                            </div>

                            <div class="pf-row pf-row-2">
                                <div class="pf-field">
                                    <label>Color Name / Hex <span class="req">*</span></label>
                                    <input type="hidden" name="colors[{{ $colorIndex }}][id]" value="{{ $color->id }}">
                                    <input type="text" name="colors[{{ $colorIndex }}][color]"
                                        class="form-control color-name-input"
                                        value="{{ $color->color }}" placeholder="e.g. Gold, #FFD700"
                                        {{ $item ? 'readonly' : '' }}>
                                </div>
                                <div class="pf-field">
                                    <label>Color Images (multi-select)</label>
                                    <div class="dropzone-area color-dropzone">
                                        <input type="file"
                                            class="color-image-input" accept="image/*" multiple>
                                        <div class="dropzone-icon" style="font-size:1.5rem;">🎨</div>
                                        <div class="dropzone-title" style="font-size:.82rem;">Drop color photos here</div>
                                        <div class="dropzone-note">Multiple images allowed — auto-compressed</div>
                                    </div>
                                    <input type="file" name="colors[{{ $colorIndex }}][images][]"
                                        class="color-hidden-input" style="display:none" accept="image/*" multiple>
                                    <div class="img-preview-grid color-preview-grid" style="margin-top:10px;">
                                        @php $colorImages = is_array($color->images) ? $color->images : (json_decode($color->images, true) ?? []); @endphp
                                        @foreach($colorImages as $img)
                                        <div class="img-preview-card">
                                            <img src="{{ asset('storage/'.$img) }}" alt="color">
                                            <span class="img-size-badge">Current</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Variants for this color --}}
                            <hr class="section-divider" style="margin:16px 0;">
                            <p class="section-heading">Size & Stock Variants</p>
                            <table class="variant-table">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Stock (Qty)</th>
                                        <th>Price (₹)</th>
                                        <th>Discount (%)</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="variant-body">
                                    @foreach($color->variants as $vIdx => $variant)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="colors[{{ $colorIndex }}][variants][{{ $vIdx }}][id]" value="{{ $variant->id }}">
                                            <input type="text" name="colors[{{ $colorIndex }}][variants][{{ $vIdx }}][size]" value="{{ $variant->size }}" placeholder="S / M / Free" {{ $item ? 'readonly' : '' }}>
                                        </td>
                                        <td><input type="number" name="colors[{{ $colorIndex }}][variants][{{ $vIdx }}][stock]" value="{{ $variant->stock }}" min="0" {{ $item ? 'readonly' : '' }}></td>
                                        <td><input type="number" step="0.01" name="colors[{{ $colorIndex }}][variants][{{ $vIdx }}][price]" value="{{ $variant->price }}" min="0" {{ $item ? 'readonly' : '' }}></td>
                                        <td><input type="number" step="0.01" name="colors[{{ $colorIndex }}][variants][{{ $vIdx }}][discount]" value="{{ $variant->discount }}" min="0" max="100" {{ $item ? 'readonly' : '' }}></td>
                                        <td><button type="button" class="btn-remove-variant" onclick="this.closest('tr').remove()" {{ $item ? 'disabled' : '' }}>✕</button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="button" class="btn-add-variant" onclick="addVariantRow(this)" {{ $item ? 'disabled' : '' }}>+ Add Size</button>
                        </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" class="btn-add-color" id="btnAddColor" onclick="addColorBlock()" {{ $item ? 'disabled' : '' }}>
                    <i class="fa-regular fa-plus"></i> Add Color Variant
                </button>
            </div>

            {{-- ACTION BAR --}}
            <div class="pf-action-bar">
                <a href="javascript:history.back()" class="btn-back">Cancel</a>
                <button type="button" class="btn-pf-save saveBtn" id="saveBtn" onclick="submitProductForm()"
                    style="{{ $item ? 'display:none' : '' }}">
                    <i class="fa-regular fa-floppy-disk"></i>
                    {{ $item ? 'Save Changes' : 'Add Product' }}
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('appendJs')
<script>
// =====================================================
// TABS
// =====================================================
document.querySelectorAll('.pf-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.pf-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.pf-tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

// =====================================================
// EDIT MODE
// =====================================================
function enableEdit() {
    document.querySelectorAll('input, textarea, select').forEach(el => {
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
    });
    document.getElementById('saveBtn').style.display = 'inline-flex';
    document.getElementById('editBtn') && (document.getElementById('editBtn').style.display = 'none');
}

// =====================================================
// SLUG AUTO-GENERATE
// =====================================================
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    document.getElementById('slug').value = slug;
    document.getElementById('slugVal').textContent = slug || 'your-product-slug';
});

// =====================================================
// IMAGE COMPRESSION UTILITY
// =====================================================
function compressImage(file, maxSizeKB = 300, quality = 0.85) {
    return new Promise((resolve) => {
        const maxBytes = maxSizeKB * 1024;
        if (file.size <= maxBytes) { resolve(file); return; }

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let { width, height } = img;

                // Optionally downscale very large images
                const MAX_DIM = 1200;
                if (width > MAX_DIM || height > MAX_DIM) {
                    const ratio = Math.min(MAX_DIM / width, MAX_DIM / height);
                    width  = Math.round(width  * ratio);
                    height = Math.round(height * ratio);
                }

                canvas.width  = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Try to get under maxBytes
                let q = quality;
                let attempt = 0;

                function tryCompress() {
                    canvas.toBlob(blob => {
                        if ((blob.size <= maxBytes) || q <= 0.2 || attempt > 6) {
                            const compressed = new File([blob], file.name, { type: 'image/jpeg' });
                            resolve(compressed);
                        } else {
                            q -= 0.1;
                            attempt++;
                            tryCompress();
                        }
                    }, 'image/jpeg', q);
                }
                tryCompress();
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function formatBytes(b) {
    if (b < 1024) return b + ' B';
    if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
    return (b/1024/1024).toFixed(2) + ' MB';
}

// =====================================================
// MAIN IMAGE DROPZONE
// =====================================================
setupDropzone({
    zone:        'mainImageZone',
    fileInput:   'main_image_input',
    hiddenInput: 'main_image',
    previewGrid: 'mainPreviewGrid',
    bar:         'mainBar',
    barFill:     'mainBarFill',
    status:      'mainStatus',
    multiple:    false
});

setupDropzone({
    zone:        'zoomedImageZone',
    fileInput:   'zoomed_image_input',
    hiddenInput: 'zoomed_image',
    previewGrid: 'zoomedPreviewGrid',
    bar:         'zoomedBar',
    barFill:     'zoomedBarFill',
    status:      'zoomedStatus',
    multiple:    false
});

function setupDropzone({ zone, fileInput, hiddenInput, previewGrid, bar, barFill, status, multiple }) {
    const zoneEl   = document.getElementById(zone);
    const inputEl  = document.getElementById(fileInput);
    const hiddenEl = document.getElementById(hiddenInput);
    const gridEl   = document.getElementById(previewGrid);
    const barEl    = document.getElementById(bar);
    const fillEl   = document.getElementById(barFill);
    const statEl   = document.getElementById(status);

    if (!zoneEl) return;

    ['dragenter','dragover'].forEach(ev => {
        zoneEl.addEventListener(ev, e => { e.preventDefault(); zoneEl.classList.add('drag-over'); });
    });
    ['dragleave','drop'].forEach(ev => {
        zoneEl.addEventListener(ev, e => { e.preventDefault(); zoneEl.classList.remove('drag-over'); });
    });
    zoneEl.addEventListener('drop', e => handleFiles(e.dataTransfer.files));
    inputEl.addEventListener('change', e => handleFiles(e.target.files));

    async function handleFiles(files) {
        if (!files.length) return;
        const file = files[0];

        // Show progress bar
        barEl.style.display = 'block';
        fillEl.style.width = '30%';
        statEl.textContent = `Compressing ${file.name}…`;

        const compressed = await compressImage(file, 300);
        fillEl.style.width = '100%';

        const ratio = file.size > 0 ? Math.round((1 - compressed.size / file.size) * 100) : 0;
        const sizeLabel = formatBytes(compressed.size);
        const isOk = compressed.size <= 300 * 1024;

        statEl.innerHTML = isOk
            ? `✅ <strong>${sizeLabel}</strong> (compressed ${ratio}%)`
            : `⚠️ <strong>${sizeLabel}</strong> — slightly over 300KB`;

        setTimeout(() => { barEl.style.display = 'none'; fillEl.style.width = '0%'; }, 1500);

        // Update hidden input with compressed file
        const dt = new DataTransfer();
        dt.items.add(compressed);
        hiddenEl.files = dt.files;

        // Show preview (clear old ones except "Current")
        const old = gridEl.querySelectorAll('.img-preview-card:not(:has(.img-size-badge[data-keep]))');
        old.forEach(el => { if (!el.querySelector('[data-keep]')) el.remove(); });

        const reader = new FileReader();
        reader.onload = function(ev) {
            const card = document.createElement('div');
            card.className = 'img-preview-card';
            card.innerHTML = `
                <img src="${ev.target.result}" alt="preview">
                <span class="img-size-badge ${isOk ? '' : 'warning'}">${sizeLabel}</span>
                <button type="button" class="img-remove-btn" onclick="this.closest('.img-preview-card').remove(); clearInput('${hiddenInput}')">✕</button>
            `;
            // Remove old new previews
            gridEl.querySelectorAll('.img-preview-card').forEach(c => {
                if (!c.querySelector('.img-size-badge')?.hasAttribute('data-keep')) c.remove();
            });
            gridEl.appendChild(card);
        };
        reader.readAsDataURL(compressed);
    }
}

function clearInput(id) {
    const el = document.getElementById(id);
    if (el) { const dt = new DataTransfer(); el.files = dt.files; }
}

// =====================================================
// COLOR BLOCKS
// =====================================================
let colorBlockIndex = {{ $item ? $item->colors->count() : 0 }};

function addColorBlock() {
    const idx = colorBlockIndex++;
    const html = `
    <div class="color-block" data-color-index="${idx}">
        <div class="color-block-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="color-swatch" id="swatch_${idx}" style="background:#e2e8f0"></div>
                <span class="color-block-title">New Color</span>
            </div>
            <button type="button" class="btn-remove-color" onclick="removeColorBlock(this)">
                <i class="fa-regular fa-trash"></i> Remove
            </button>
        </div>
        <div class="pf-row pf-row-2">
            <div class="pf-field">
                <label>Color Name / Hex <span class="req">*</span></label>
                <input type="text" name="colors[${idx}][color]"
                    class="form-control color-name-input" data-idx="${idx}"
                    placeholder="e.g. Gold, Rose Gold, #FFD700" oninput="updateSwatch(this)">
            </div>
            <div class="pf-field">
                <label>Color Images (multi-select)</label>
                <div class="dropzone-area color-dropzone" id="colorZone_${idx}">
                    <input type="file" id="colorInput_${idx}" class="color-image-input" accept="image/*" multiple>
                    <div class="dropzone-icon" style="font-size:1.5rem;">🎨</div>
                    <div class="dropzone-title" style="font-size:.82rem;">Drop color photos here</div>
                    <div class="dropzone-note">Multiple images — auto-compressed to ≤300KB each</div>
                </div>
                <input type="file" name="colors[${idx}][images][]" id="colorHidden_${idx}" class="color-hidden-input" style="display:none" accept="image/*" multiple>
                <div class="compress-status" id="colorStatus_${idx}"></div>
                <div class="img-preview-grid color-preview-grid" id="colorPreview_${idx}" style="margin-top:10px;"></div>
            </div>
        </div>
        <hr class="section-divider" style="margin:16px 0;">
        <p class="section-heading">Size & Stock Variants</p>
        <table class="variant-table">
            <thead>
                <tr>
                    <th>Size</th>
                    <th>Stock (Qty)</th>
                    <th>Price (₹)</th>
                    <th>Discount (%)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="variant-body" id="variantBody_${idx}">
                <tr>
                    <td><input type="text" name="colors[${idx}][variants][0][size]" placeholder="S / M / Free"></td>
                    <td><input type="number" name="colors[${idx}][variants][0][stock]" min="0" placeholder="0"></td>
                    <td><input type="number" step="0.01" name="colors[${idx}][variants][0][price]" min="0" placeholder="0.00"></td>
                    <td><input type="number" step="0.01" name="colors[${idx}][variants][0][discount]" min="0" max="100" placeholder="0"></td>
                    <td><button type="button" class="btn-remove-variant" onclick="this.closest('tr').remove()">✕</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn-add-variant" onclick="addVariantRow(this)">+ Add Size</button>
    </div>`;

    document.getElementById('colorsContainer').insertAdjacentHTML('beforeend', html);
    setupColorDropzone(idx);
}

function setupColorDropzone(idx) {
    const zoneEl   = document.getElementById(`colorZone_${idx}`);
    const inputEl  = document.getElementById(`colorInput_${idx}`);
    const hiddenEl = document.getElementById(`colorHidden_${idx}`);
    const gridEl   = document.getElementById(`colorPreview_${idx}`);
    const statEl   = document.getElementById(`colorStatus_${idx}`);

    if (!zoneEl) return;

    ['dragenter','dragover'].forEach(ev => {
        zoneEl.addEventListener(ev, e => { e.preventDefault(); zoneEl.classList.add('drag-over'); });
    });
    ['dragleave','drop'].forEach(ev => {
        zoneEl.addEventListener(ev, e => { e.preventDefault(); zoneEl.classList.remove('drag-over'); });
    });
    zoneEl.addEventListener('drop', e => handleColorFiles(e.dataTransfer.files));
    inputEl.addEventListener('change', e => handleColorFiles(e.target.files));

    async function handleColorFiles(files) {
        if (!files.length) return;
        statEl.textContent = `Compressing ${files.length} image(s)…`;
        const dt = new DataTransfer();

        for (let i = 0; i < files.length; i++) {
            const compressed = await compressImage(files[i], 300);
            dt.items.add(compressed);

            const reader = new FileReader();
            reader.onload = function(ev) {
                const card = document.createElement('div');
                card.className = 'img-preview-card';
                const sizeLabel = formatBytes(compressed.size);
                const isOk = compressed.size <= 300 * 1024;
                card.innerHTML = `
                    <img src="${ev.target.result}" alt="color-img">
                    <span class="img-size-badge ${isOk ? '' : 'warning'}">${sizeLabel}</span>
                    <button type="button" class="img-remove-btn" onclick="this.closest('.img-preview-card').remove()">✕</button>
                `;
                gridEl.appendChild(card);
            };
            reader.readAsDataURL(compressed);
        }

        hiddenEl.files = dt.files;
        statEl.innerHTML = `✅ <strong>${files.length}</strong> image(s) compressed & ready`;
    }
}

function updateSwatch(input) {
    const idx = input.dataset.idx;
    const swatch = document.getElementById(`swatch_${idx}`);
    if (!swatch) return;
    const val = input.value.trim();
    if (val.startsWith('#') && val.length >= 4) {
        swatch.style.background = val;
    } else {
        swatch.style.background = '#e2e8f0';
    }
}

function removeColorBlock(btn) {
    btn.closest('.color-block').remove();
}

function addVariantRow(btn) {
    const body = btn.previousElementSibling.querySelector('.variant-body');
    const idx = btn.closest('.color-block').dataset.colorIndex;
    const rowCount = body.querySelectorAll('tr').length;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="colors[${idx}][variants][${rowCount}][size]" placeholder="S / M / Free"></td>
        <td><input type="number" name="colors[${idx}][variants][${rowCount}][stock]" min="0" placeholder="0"></td>
        <td><input type="number" step="0.01" name="colors[${idx}][variants][${rowCount}][price]" min="0" placeholder="0.00"></td>
        <td><input type="number" step="0.01" name="colors[${idx}][variants][${rowCount}][discount]" min="0" max="100" placeholder="0"></td>
        <td><button type="button" class="btn-remove-variant" onclick="this.closest('tr').remove()">✕</button></td>
    `;
    body.appendChild(tr);
}

// =====================================================
// FORM SUBMIT WITH AXIOS
// =====================================================
function submitProductForm() {
    const form = document.getElementById('productForm');
    const formData = new FormData(form);

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Saving…';

    axios.post(form.action, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }).then(res => {
        if (res.data.success) {
            toastr.success(res.data.message || 'Product saved!');
            setTimeout(() => { window.location.href = res.data.redirect || '/admin/products'; }, 1200);
        } else {
            toastr.error(res.data.message || 'Something went wrong.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> {{ $item ? "Save Changes" : "Add Product" }}';
        }
    }).catch(err => {
        const errors = err.response?.data?.errors;
        if (errors) {
            const msgs = Object.values(errors).flat().join('<br>');
            toastr.error(msgs, 'Validation Error', { timeOut: 6000 });
        } else {
            toastr.error('Failed to save product. Please try again.');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> {{ $item ? "Save Changes" : "Add Product" }}';
    });
}

// Init for existing color dropzones on edit
@if($item)
    @foreach($item->colors as $colorIndex => $color)
        setupColorDropzone({{ $colorIndex }});
    @endforeach
@endif
</script>
@endpush