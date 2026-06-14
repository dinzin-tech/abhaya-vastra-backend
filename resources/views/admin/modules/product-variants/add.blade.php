@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Product Variant | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Product Variant" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Product Variant</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('product-variants.store') }}" method="post" 
                  enctype="multipart/form-data" onsubmit="return submitVariantForm(this)">
                @csrf
                <input type="hidden" name="id" value="{{ $item ? $item->id : '' }}">
                {{-- REMOVED: <input type="hidden" name="removed_images" id="removed_images" value="[]"> --}}

                <div class="card__title-wrap mb-20 d-flex justify-content-end">
                    <a href="{{ route('product-variants.index') }}" class="btn btn-info me-2">
                        <i class="la la-arrow-left"></i> Back
                    </a>
                    <button type="button" id="editBtn" class="btn btn-primary" 
                            style="{{ $item ? '' : 'display:none;' }}" onclick="enableEdit(this);">
                        Edit
                    </button>
                </div>

                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="product_id">Product <span>*</span></label>
                            </div>
                            <div class="form__input">
                                {{-- The 'productColors' loop is removed from here as it's now populated dynamically --}}
                                <select name="product_id" id="product_id" class="form-control select2" 
                                        {{ $item ? 'disabled' : '' }} required>
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
                    </div>
                </div>

                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="color_id">Color <span>*</span></label></div>
                            <div class="form__input">
                                <select name="color_id" id="color_id" class="form-control" {{ $item ? 'disabled' : '' }} required>
                                    {{-- Initial options are placeholders, they will be loaded by JS --}}
                                    <option value="">Select Product First</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="size">Size <span>*</span></label></div>
                            <div class="form__input">
                                <select name="size" id="size" class="form-control" {{ $item ? 'disabled' : '' }} required>
                                    @php $sizes = ['XS','S','M','L','XL','XXL']; @endphp
                                    <option value="">Select Size</option>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size }}" {{ ($item && $item->size == $size) ? 'selected' : '' }}>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="stock">Stock</label></div>
                            <div class="form__input">
                                <input type="number" name="stock" id="stock" class="form-control"
                                       value="{{ $item->stock ?? 0 }}" {{ $item ? 'readonly' : '' }}>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
    <div class="from__input-box">
        <div class="form__input-title"><label for="weight">Weight</label></div>
        <div class="form__input">
            <input type="number" name="weight" id="weight" class="form-control"
                   step="0.01" min="0"
                   value="{{ $item->weight ?? 0 }}" {{ $item ? 'readonly' : '' }}>
        </div>
    </div>
</div>


                    <!-- <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="price">Variant Price <span>*</span></label></div>
                            <div class="form__input">
                                <input type="number" step="0.01" name="price" id="price" class="form-control"
                                       value="{{ $item->price ?? '' }}" {{ $item ? 'readonly' : '' }} required>
                            </div>
                        </div>
                    </div> -->
                      <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-4">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="price">Price<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="number" step="0.01" name="price" id="price" class="form-control"
                                    value="{{ $item->price ?? '' }}" {{ $item ? 'readonly' : '' }} required />
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="discount">Discount (%)</label>
                            </div>
                            <div class="form__input">
                                <input type="number" step="0.01" name="discount" id="discount" class="form-control"
                                    value="{{ $item->discount ?? '' }}" {{ $item ? 'readonly' : '' }} />
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="total_price">Total Price</label>
                            </div>
                            <div class="form__input">
                                <input type="number" step="0.01" id="total_price" class="form-control"
                                    value="{{ $item->total_price ?? ($item->price ?? '') }}"
                                    readonly />
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                {{-- REMOVED: Variant Images section --}}

                <button class="btn btn-primary w-auto saveBtn mt-5" type="submit" style="{{ $item ? 'display:none;' : '' }}">
                    {{ $item ? 'Update' : 'Add' }}
                </button>
            </form>
        </div>
    </div>
</div>
@stop

@push('appendJs')
<script src="{{ asset('assets/js/plugins/select2.full.min.js') }}"></script>

<script>
// Define the AJAX endpoint URL using the route helper
const fetchColorUrl = "{{ route('product-variants.get-colors') }}";

// var enableEdit = function(ele){
//     $('input, textarea, select').attr('readonly', false).attr('disabled', false);
    
//     // In edit mode, if the product/color/size fields should be changeable, re-enable them.
//     // However, typically product and color links are kept disabled/readonly after creation.
//     // If you need them to be editable, you can specifically re-enable them here.
//     $('#product_id').prop('disabled', false).trigger('change');
//     $('#color_id').prop('disabled', false).trigger('change');
    
//     $('.saveBtn').show();
//     $(ele).hide();
// }


var enableEdit = function(ele){
    $('input, textarea, select').attr('readonly', false).attr('disabled', false);

    // Show the Save button and hide Edit
    $('.saveBtn').show();
    $(ele).hide();

    // Show the remove buttons if any image wrappers exist
    document.querySelectorAll('.customize-img-wrapper button').forEach(btn=>{
        btn.classList.remove('d-none');
    });

    // 🔹 Re-load colors for the selected product to preserve selection
    const productId = $('#product_id').val();
    const selectedColorId = "{{ $item->color_id ?? '' }}"; // Blade variable
    if(productId){
        loadProductColors(productId, selectedColorId);
    }
}

@if(!$item)
enableEdit();
@endif


// Function to handle fetching and updating the Color dropdown
function loadProductColors(productId, selectedColorId = null) {
    const colorSelect = $('#color_id');
    
    // Clear existing options and show a loading message
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
                    // Check if the current color should be selected (for Edit mode)
                    const isSelected = selectedColorId && selectedColorId == color.id ? 'selected' : '';
                    colorSelect.append(`<option value="${color.id}" ${isSelected}>${color.color}</option>`);
                });
            } else {
                colorSelect.append('<option value="">No Colors Found for this Product</option>');
            }
            
            // Re-enable the dropdown
            colorSelect.prop('disabled', false);
            
            // Re-trigger select2 refresh if necessary
            // colorSelect.select2();
        })
        .catch(error => {
            console.error("Error fetching colors:", error);
            colorSelect.empty().append('<option value="">Error Loading Colors</option>');
            colorSelect.prop('disabled', true);
        });
}

// Initialization Logic
$(document).ready(function() {
    // 1. Attach listener to the Product dropdown
    $('#product_id').on('change', function() {
        const selectedProductId = $(this).val();
        loadProductColors(selectedProductId);
    });

    // 2. Initial load for Edit mode or pre-selected products
    const initialProductId = $('#product_id').val();
    const initialColorId = "{{ $item->color_id ?? '' }}";
    
    // If we have a product selected initially, load the colors
    if (initialProductId) {
        // Only load if in Create mode OR in Edit mode and the dropdown is currently enabled/not explicitly disabled
        @if(!$item || ($item && !($item ? 'disabled' : '')))
            loadProductColors(initialProductId, initialColorId);
        @endif
    }
});





function submitVariantForm(form){
    let formData = new FormData(form);

    // Disable the submit button to prevent double-click
    $('.saveBtn').prop('disabled', true);

    axios.post(form.action, formData, {
        // Content-Type is correct for FormData
    }).then(res=>{
        if(res.data.redirect){
            window.location.href = res.data.redirect;
        }else{
            alert(res.data.message);
        }
    }).catch(err=>{
        alert(err.response?.data?.message || 'Something went wrong');
    }).finally(()=>{
        // Re-enable the submit button
        $('.saveBtn').prop('disabled', false);
    });

    return false;
}

 function calculateTotal(){
        let price = parseFloat($('#price').val()) || 0;
        let discount = parseFloat($('#discount').val()) || 0;
        let total = price;

        if(discount > 0){
            total = price - (price * (discount/100));
        }
        $('#total_price').val(total.toFixed(2));
    }

    $('#price, #discount').on('input', calculateTotal);

    // run once on page load
    calculateTotal();
</script>
@endpush