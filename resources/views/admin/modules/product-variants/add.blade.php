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
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pvf-row pvf-row-2">
                <div class="pvf-field">
                    <label for="color_id">Color <span class="req">*</span></label>
                    <select name="color_id" id="color_id" {{ $item ? 'disabled' : '' }} required>
                        <option value="">Select Product First</option>
                    </select>
                </div>

                <div class="pvf-field">
                    <label for="size">Size <span class="req">*</span></label>
                    <input type="text" name="size" id="size" class="form-control"
                           value="{{ $item->size ?? '' }}" {{ $item ? 'readonly' : '' }} required
                           placeholder="e.g. S, M, XL, Free Size">
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

    function loadProductColors(productId, selectedColorId = null) {
        const colorSelect = $('#color_id');
        colorSelect.empty().append('<option value="">Loading Colors...</option>').prop('disabled', true);
        
        if (!productId) {
            colorSelect.empty().append('<option value="">Select Product First</option>');
            return;
        }

        axios.post(fetchColorUrl, { product_id: productId })
            .then(response => {
                colorSelect.empty().append('<option value="">Select Color</option>');
                if (response.data.length > 0) {
                    response.data.forEach(color => {
                        const isSelected = selectedColorId && selectedColorId == color.id ? 'selected' : '';
                        colorSelect.append(`<option value="${color.id}" ${isSelected}>${color.color}</option>`);
                    });
                } else {
                    colorSelect.append('<option value="">No Colors Found</option>');
                }
                colorSelect.prop('disabled', false);
            })
            .catch(error => {
                colorSelect.empty().append('<option value="">Error Loading Colors</option>');
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