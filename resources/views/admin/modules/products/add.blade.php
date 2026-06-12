@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Product | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Product" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Product</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('products.store') }}" method="post" enctype="multipart/form-data" onsubmit="return updateProfileimgs(this)">
                
                @csrf
                <input type="hidden" name="id" value="{{ $item->id ?? '' }}">

                <div class="card__title-wrap mb-20">
                    <div class="text-end">
                        <a href="javascript:history.back()" class="btn btn-info kt-margin-r-10">
                            <i class="la la-arrow-left"></i> Back
                        </a>
                        @if($item)
                            <button type="button" id="editBtn" class="btn btn-primary" onclick="enableEdit(this);">Edit</button>
                        @endif
                    </div>
                </div>

                <!-- Category Select -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="category_id">Category<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <select name="category_id" id="category_id" class="form-control" {{ $item ? 'disabled' : '' }} required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ optional($item)->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Gender Select -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="gender">Gender<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <select id="gender" name="gender" class="form-control" {{ $item ? 'disabled' : '' }} required>
                                    <option value="male" {{ optional($item)->gender == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ optional($item)->gender == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="unisex" {{ optional($item)->gender == 'unisex' ? 'selected' : '' }}>Unisex</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Name Input -->
                <!-- <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="name">Product Name<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="text" id="name" name="name" class="form-control" value="{{ $item->name ?? '' }}" {{ $item ? 'readonly' : '' }} required />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="slug">Slug<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="text" id="slug" name="slug" class="form-control" value="{{ $item->slug ?? '' }}" readonly required />
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
    <!-- Product Name -->
    <div class="col-lg-6">
        <div class="from__input-box">
            <div class="form__input-title">
                <label for="name">Product Name<span>*</span></label>
            </div>
            <div class="form__input">
                <input type="text" id="name" name="name" class="form-control" 
                    value="{{ $item->name ?? '' }}" {{ $item ? 'readonly' : '' }} required />
            </div>
        </div>
    </div>

    <!-- Slug -->
    <div class="col-lg-6">
        <div class="from__input-box">
            <div class="form__input-title">
                <label for="slug">Slug<span>*</span></label>
            </div>
            <div class="form__input">
                <input type="text" id="slug" name="slug" class="form-control" 
                    value="{{ $item->slug ?? '' }}" readonly required />
            </div>
        </div>
    </div>
</div>


                <!-- Description -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="description">Description</label>
                            </div>
                            <div class="form__input">
                                <textarea name="description" id="description" class="form-control" rows="5" {{ $item ? 'readonly' : '' }}>{{ $item->description ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price, Discount (%) & Total Price -->
                <!-- <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
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
                </div> -->

                <!-- Best Seller Checkbox -->
                <!-- <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="best_seller" id="best_seller"
                                    value="1" {{ optional($item)->best_seller ? 'checked' : '' }} {{ $item ? 'disabled' : '' }}>
                                <label class="form-check-label" for="best_seller">Best Seller</label>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
    <!-- Best Seller -->
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="from__input-box">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="best_seller" id="best_seller"
                    value="1" {{ optional($item)->best_seller ? 'checked' : '' }} {{ $item ? 'disabled' : '' }}>
                <label class="form-check-label" for="best_seller">Best Seller</label>
            </div>
        </div>
    </div>

    <!-- Featured Product -->
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="from__input-box">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                    value="1" {{ optional($item)->is_featured ? 'checked' : '' }} {{ $item ? 'disabled' : '' }}>
                <label class="form-check-label" for="is_featured">Featured Product</label>
            </div>
        </div>
    </div>
</div>



                <!-- Main Image -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="main_image">Main Image</label>
                            </div>
                            <div class="form__input">
                                <input type="file" name="main_image" id="main_image" class="form-control" accept="image/*" onchange="previewImage(this,'mainPreview')" {{ $item ? '' : 'required' }} />
                                <div class="mt-2">
                                    <img id="mainPreview"
                                        @if($item && $item->main_image)
                                            src="{{ asset('storage/'.$item->main_image) }}"
                                            style="width:150px;height:150px;object-fit:cover;border:1px solid #ccc;"
                                        @else
                                            style="display:none;width:150px;height:150px;object-fit:cover;border:1px solid #ccc;"
                                        @endif
                                    />
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Zoomed Image -->
                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="zoomed_image">Zoomed Image</label>
                            </div>
                            <div class="form__input">
                                <input type="file" name="zoomed_image" id="zoomed_image" class="form-control" accept="image/*" onchange="previewImage(this,'zoomPreview')" {{ $item ? '' : 'required' }} />
                               <div class="mt-2">
                                <img id="zoomPreview"
                                    @if($item && $item->zoomed_image)
                                        src="{{ asset('storage/'.$item->zoomed_image) }}"
                                        style="width:150px;height:150px;object-fit:cover;border:1px solid #ccc;"
                                    @else
                                        style="display:none;width:150px;height:150px;object-fit:cover;border:1px solid #ccc;"
                                    @endif
                                />
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-auto saveBtn mt-5" type="submit" style="{{ $item ? 'display:none;' : '' }}">
                    {{ $item ? 'Update' : 'Add' }}
                </button>

            </form>
        </div>
    </div>
</div>
@stop

@push('appendJs')
<script src="{{ asset('assets/js/plugins/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/plugins/select2.full.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
<script src="{{ asset('assets/js/post-jobs.js') }}"></script>
<script src="{{ asset('assets/js/save-file.js') }}" type="text/javascript" charset="utf-8"></script>

<script>
    // Enable editing for existing product
    var enableEdit = function(ele){
        $('input, textarea, select').attr('readonly', false).attr('disabled', false);
        $('.saveBtn').show();
        $(ele).hide();
    }

    // Auto-generate slug from name
    $('#name').on('input', function(){
        let slug = $(this).val().toLowerCase().replace(/\s+/g,'-').replace(/[^\w-]+/g,'');
        $('#slug').val(slug);
    });

    // Image preview
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }


    // Auto calculate Total Price based on Price & Discount
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

    @if(!$item)
        enableEdit();
    @endif
</script>
@endpush