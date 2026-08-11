@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Product Variant | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Product Variant" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@push('appendCss')
<style>
    .pvf-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .pvf-header h4 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .pvf-header .btn-back {
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
    .pvf-header .btn-back:hover { background:#f1f5f9; color:#0f172a; }

    .pvf-row { display:grid; gap:20px; margin-bottom:20px; }
    .pvf-row-1 { grid-template-columns: 1fr; }
    .pvf-row-2 { grid-template-columns: 1fr 1fr; }
    .pvf-row-3 { grid-template-columns: 1fr 1fr 1fr; }
    @media(max-width:768px) {
        .pvf-row-2, .pvf-row-3 { grid-template-columns:1fr; }
    }

    .pvf-field label {
        display:block; font-size:0.8rem; font-weight:600;
        color:#374151; margin-bottom:6px; letter-spacing:.3px;
    }
    .pvf-field label .req { color:#ef4444; margin-left:2px; }
    .pvf-field .form-control, .pvf-field select {
        width:100%; padding:10px 14px; font-size:0.9rem;
        border:1.5px solid #e2e8f0; border-radius:8px;
        background:#f8fafc; color:#1e293b; transition: border-color .2s, box-shadow .2s;
        outline:none;
    }
    .pvf-field .form-control:focus, .pvf-field select:focus {
        border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1);
        background:#fff;
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
</style>
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="pvf-header">
        <div>
            <h4>
                <i class="fa-regular fa-boxes-stacked me-2" style="color:#6366f1"></i>
                {{ $item ? 'Update Product Variant' : 'Add New Product Variant' }}
            </h4>
        </div>
        <a href="{{ route('product-variants.index') }}" class="btn-back">
            <i class="fa-regular fa-arrow-left"></i> Back to Variants
        </a>
    </div>

    <div class="card__wrapper" style="border-radius:16px;padding:28px;">
        @if($item)
        <div class="alert d-flex align-items-center gap-2 mb-4" style="background:#fef3c7;border:1.5px solid #fcd34d;border-radius:10px;font-size:.85rem;color:#92400e;padding:12px 18px;">
            <i class="fa-solid fa-lock"></i>
            <span>Form is in <strong>view mode</strong>. Click <strong>Enable Edit</strong> to make changes.</span>
            <button type="button" id="editBtn" class="btn-pf-edit ms-auto" onclick="enableEdit(this)">
                <i class="fa-regular fa-pen"></i> Enable Edit
            </button>
        </div>
        @endif

        <form id="variantForm" action="{{ route('product-variants.store') }}" method="post" onsubmit="return submitForm()">
            @csrf
            <input type="hidden" name="id" value="{{ $item ? $item->id : '' }}">

            <div class="pvf-row pvf-row-1">
                <div class="pvf-field">
                    <label for="product_id">Product <span class="req">*</span></label>
                    <select name="product_id" id="product_id" {{ $item ? 'disabled' : '' }} required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                {{ ($item && $item->product_id == $product->id) ? 'selected' : '' }}>
                                {{ $product->is_combo ? '[Combo] 👥 ' : '' }}{{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Combo Size Helper Block --}}
            <div id="combo-size-helper" style="display:none; margin-bottom: 20px; background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 12px; padding: 18px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span style="color: #0369a1; font-weight: 700; font-size: 0.88rem;">
                        <i class="fa-solid fa-people-group me-1"></i> Combo Product Size Assistant
                    </span>
                    <span class="badge bg-info text-dark" style="font-size:0.72rem;">Combo Sizing Active</span>
                </div>
                <p class="text-muted mb-2" style="font-size: 0.78rem;">
                    Select Men's and Women's sizes below to automatically populate or construct the size format for this combo variant:
                </p>
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-md-5">
                        <label class="form-label mb-1 fw-bold" style="font-size:0.78rem; color:#2563eb;">👔 Men's Size</label>
                        <select id="helper_male_size" class="form-select form-select-sm" onchange="updateComboSizeFromHelper()">
                            <option value="">Select Men's Size</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label mb-1 fw-bold" style="font-size:0.78rem; color:#db2777;">👗 Women's Size</label>
                        <select id="helper_female_size" class="form-select form-select-sm" onchange="updateComboSizeFromHelper()">
                            <option value="">Select Women's Size</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-primary w-100" onclick="applyComboSizePreset()">
                            <i class="fa-solid fa-check me-1"></i> Apply
                        </button>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap" id="combo-preset-badges"></div>
            </div>

            <div class="pvf-row pvf-row-2">
                <div class="pvf-field">
                    <label for="color_id">Color <span class="req">*</span></label>
                    <select name="color_id" id="color_id" {{ $item ? 'disabled' : '' }} required>
                        <option value="">Select Product First</option>
                    </select>
                </div>

                <div class="pvf-field">
                    <label for="size">Size <span class="req">*</span> <small id="size_hint_label" class="text-muted fw-normal"></small></label>
                    <input type="text" name="size" id="size" class="form-control"
                           value="{{ $item->size ?? '' }}" {{ $item ? 'readonly' : '' }} required
                           placeholder="e.g. S, M, XL, or Combo size e.g. M-S">
                </div>
            </div>

            <div class="pvf-row pvf-row-2">
                <div class="pvf-field">
                    <label for="stock">Stock (Quantity) <span class="req">*</span></label>
                    <input type="number" name="stock" id="stock" class="form-control"
                           value="{{ $item->stock ?? 0 }}" {{ $item ? 'readonly' : '' }} required min="0">
                </div>

                <div class="pvf-field">
                    <label for="weight">Weight (kg)</label>
                    <input type="number" step="0.01" name="weight" id="weight" class="form-control"
                           value="{{ $item->weight ?? '' }}" {{ $item ? 'readonly' : '' }} min="0" placeholder="e.g. 0.25">
                </div>
            </div>

            <div class="pvf-row pvf-row-3">
                <div class="pvf-field">
                    <label for="price">Price (₹) <span class="req">*</span></label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control"
                           value="{{ $item->price ?? '' }}" {{ $item ? 'readonly' : '' }} required min="0">
                </div>

                <div class="pvf-field">
                    <label for="discount">Discount (%)</label>
                    <input type="number" step="0.01" name="discount" id="discount" class="form-control"
                           value="{{ $item->discount ?? 0 }}" {{ $item ? 'readonly' : '' }} min="0" max="100">
                </div>

                <div class="pvf-field">
                    <label for="total_price">Calculated Total Price (₹)</label>
                    <input type="number" step="0.01" id="total_price" class="form-control"
                           value="{{ $item->total_price ?? '' }}" readonly style="background:#f1f5f9;font-weight:700;">
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px;">
                <a href="{{ route('product-variants.index') }}" class="btn-back" style="padding:11px 24px;border-radius:10px;">Cancel</a>
                <button type="submit" class="btn-pf-save saveBtn" id="saveBtn" style="{{ $item ? 'display:none;' : '' }}">
                    <i class="fa-regular fa-floppy-disk"></i>
                    {{ $item ? 'Save Changes' : 'Add Variant' }}
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('appendJs')
<script>
    const fetchColorUrl = "{{ route('product-variants.get-colors') }}";

    function enableEdit(btn) {
        document.querySelectorAll('input:not([type="hidden"]), select').forEach(el => {
            el.removeAttribute('readonly');
            el.removeAttribute('disabled');
        });
        document.getElementById('saveBtn').style.display = 'inline-flex';
        btn.style.display = 'none';

        const productId = $('#product_id').val();
        const selectedColorId = "{{ $item->color_id ?? '' }}";
        if(productId){
            loadProductColors(productId, selectedColorId);
        }
    }

    function updateComboSizeFromHelper() {
        const male = $('#helper_male_size').val();
        const female = $('#helper_female_size').val();
        if (male && female) {
            $('#size').val(`👔 M: ${male} · 👗 F: ${female}`);
        } else if (male) {
            $('#size').val(`Men's ${male}`);
        } else if (female) {
            $('#size').val(`Women's ${female}`);
        }
    }

    function setComboSizeDirect(val) {
        $('#size').val(val);
    }

    function applyComboSizePreset() {
        updateComboSizeFromHelper();
    }

    function loadProductColors(productId, selectedColorId = null) {
        const colorSelect = $('#color_id');
        const helperBox = $('#combo-size-helper');
        colorSelect.empty().append('<option value="">Loading Colors...</option>').prop('disabled', true);
        
        if (!productId) {
            colorSelect.empty().append('<option value="">Select Product First</option>');
            helperBox.hide();
            return;
        }

        axios.post(fetchColorUrl, { product_id: productId })
            .then(response => {
                const resData = response.data;
                const colorsList = Array.isArray(resData) ? resData : (resData.colors || []);
                const isCombo = resData.is_combo || false;
                const maleSizes = resData.male_sizes || [];
                const femaleSizes = resData.female_sizes || [];

                colorSelect.empty().append('<option value="">Select Color</option>');
                if (colorsList.length > 0) {
                    colorsList.forEach(color => {
                        const isSelected = selectedColorId && selectedColorId == color.id ? 'selected' : '';
                        colorSelect.append(`<option value="${color.id}" ${isSelected}>${color.color}</option>`);
                    });
                } else {
                    colorSelect.append('<option value="">No Colors Found</option>');
                }
                colorSelect.prop('disabled', false);

                // Handle Combo Assistant UI
                if (isCombo) {
                    $('#size_hint_label').text('(Combo product sizes available below)');
                    helperBox.show();
                    
                    const mSelect = $('#helper_male_size').empty().append('<option value="">Select Men\'s Size</option>');
                    const fSelect = $('#helper_female_size').empty().append('<option value="">Select Women\'s Size</option>');

                    (maleSizes.length > 0 ? maleSizes : ['S','M','L','XL','XXL']).forEach(s => {
                        mSelect.append(`<option value="${s}">${s}</option>`);
                    });

                    (femaleSizes.length > 0 ? femaleSizes : ['XS','S','M','L','XL']).forEach(s => {
                        fSelect.append(`<option value="${s}">${s}</option>`);
                    });

                    // Quick presets
                    const badgesContainer = $('#combo-preset-badges').empty();
                    badgesContainer.append('<span class="text-muted style-small" style="font-size:0.75rem;">Quick Presets:</span>');
                    ['Free Combo Size', 'All Sizes Combo', 'Standard Set'].forEach(p => {
                        badgesContainer.append(`<button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:0.72rem;" onclick="setComboSizeDirect('${p}')">${p}</button>`);
                    });
                } else {
                    $('#size_hint_label').text('');
                    helperBox.hide();
                }
            })
            .catch(error => {
                colorSelect.empty().append('<option value="">Error Loading Colors</option>');
                helperBox.hide();
            });
    }

    $(document).ready(function() {
        $('#product_id').on('change', function() {
            loadProductColors($(this).val());
        });

        const initialProductId = $('#product_id').val();
        const initialColorId = "{{ $item->color_id ?? '' }}";
        if (initialProductId) {
            loadProductColors(initialProductId, initialColorId);
        }

        // Live calculation
        function calculateTotal() {
            let price = parseFloat($('#price').val()) || 0;
            let discount = parseFloat($('#discount').val()) || 0;
            let total = price;
            if(discount > 0){
                total = price - (price * (discount/100));
            }
            $('#total_price').val(total.toFixed(2));
        }

        $('#price, #discount').on('input', calculateTotal);
        calculateTotal();
    });

    function submitForm() {
        event.preventDefault();
        const form = document.getElementById('variantForm');
        const formData = new FormData(form);

        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Saving…';

        axios.post(form.action, formData)
            .then(res => {
                if (res.data.success) {
                    toastr.success(res.data.message || 'Saved successfully!');
                    setTimeout(() => { window.location.href = res.data.redirect || '/admin/product-variants'; }, 1000);
                } else {
                    toastr.error(res.data.message || 'Something went wrong.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> Save';
                }
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                if (errors) {
                    const msgs = Object.values(errors).flat().join('<br>');
                    toastr.error(msgs, 'Validation Error', { timeOut: 6000 });
                } else {
                    toastr.error('Failed to save variant.');
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-regular fa-floppy-disk"></i> Save';
            });

        return false;
    }
</script>
@endpush