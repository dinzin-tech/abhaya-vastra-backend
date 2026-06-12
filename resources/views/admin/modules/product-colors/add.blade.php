@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Product Color | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Product Color" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Product Color</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('product-colors.store') }}" method="post" 
                  enctype="multipart/form-data" onsubmit="return submitColorForm(this)">
                @csrf
                <input type="hidden" name="id" value="{{ $item ? $item->id : '' }}">
                <input type="hidden" name="removed_images" id="removed_images" value="[]">

                <div class="card__title-wrap mb-20 d-flex justify-content-end">
                    <a href="{{ route('product-colors.index') }}" class="btn btn-info me-2">
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
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="color">Color <span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="text" name="color" id="color" class="form-control" 
                                       value="{{ $item->color ?? '' }}" {{ $item ? 'readonly' : '' }} required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="images">Images (min 1, max 4)</label></div>
                            <div class="form__input">
                                <input type="file" id="images" name="images[]" multiple accept="image/*"
                                       class="form-control" onchange="previewImages(this)" {{ $item ? 'disabled' : '' }}>
                                <div id="imagesPreview" class="mt-3 d-flex flex-wrap gap-2">
                                    @if($item && $item->images)
                                        @foreach(is_array($item->images) ? $item->images : json_decode($item->images, true) as $img)
                                            <div class="img-wrapper position-relative me-2 mb-2" style="width:100px;height:100px;">
                                                <img src="{{ asset('storage/'.$img) }}" style="width:100%;height:100%;object-fit:cover;border:1px solid #ccc;">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 d-none" 
                                                        onclick="removeOldImage(this,'{{ $img }}')">×</button>
                                            </div>
                                        @endforeach
                                    @endif
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
<script src="{{ asset('assets/js/plugins/select2.full.min.js') }}"></script>

<script>
let removedOldImages = [];
let newImages = [];

var enableEdit = function(ele){
    // Enable all form elements except the hidden inputs and the select2 dropdown (which needs a separate reinitialization if it was disabled)
    $('input:not([type="hidden"]), textarea, select:not(.select2)').attr('readonly', false).attr('disabled', false);
    // Specifically enable the select2 product dropdown
    $('#product_id').prop('disabled', false).trigger('change');
    
    $('.saveBtn').show();
    $(ele).hide();
    document.querySelectorAll('.img-wrapper button').forEach(btn=>{
        btn.classList.remove('d-none');
    });
}

@if(!$item)
enableEdit();
@endif

function previewImages(input){
    const previewContainer = document.getElementById('imagesPreview');
    const filesArray = Array.from(input.files);

    // Filter out files that would exceed the 4 image limit
    const currentOldCount = previewContainer.querySelectorAll('.img-wrapper > img:not([src^="data:"])').length; // Count existing saved images
    const currentNewCount = newImages.length; // Count new images already staged
    
    const filesToAdd = [];
    
    for(const file of filesArray) {
        if (currentOldCount + currentNewCount + filesToAdd.length < 4) {
            filesToAdd.push(file);
        } else {
            alert('Maximum 4 images allowed. Not all selected images were added.');
            break; 
        }
    }


    filesToAdd.forEach(file => {
        newImages.push(file);
        const reader = new FileReader();
        reader.onload = e => {
            const wrapper = document.createElement('div');
            wrapper.classList.add('img-wrapper','position-relative','me-2','mb-2');
            wrapper.style.width = '100px';
            wrapper.style.height = '100px';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.border = '1px solid #ccc';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.classList.add('btn','btn-danger','btn-sm','position-absolute','top-0','end-0');
            btn.innerHTML = '×';
            btn.onclick = () => {
                const index = newImages.indexOf(file);
                if(index > -1) newImages.splice(index,1);
                wrapper.remove();
            }

            wrapper.appendChild(img);
            wrapper.appendChild(btn);
            previewContainer.appendChild(wrapper);
        }
        reader.readAsDataURL(file);
    });

    input.value = ''; // Clear the file input for re-selection 
}

function removeOldImage(el,imgPath){
    removedOldImages.push(imgPath);
    el.parentElement.remove();
    document.getElementById('removed_images').value = JSON.stringify(removedOldImages);
}

function submitColorForm(form){
    let formData = new FormData(form);

    newImages.forEach(file => formData.append('images[]',file));

    let totalImages = document.querySelectorAll('#imagesPreview .img-wrapper').length;

    if(totalImages < 1){
        alert('Please select at least one image');
        return false;
    }
    
    // Disable the submit button to prevent double-click
    $('.saveBtn').prop('disabled', true);

    axios.post(form.action, formData, {
        headers:{'Content-Type':'multipart/form-data'}
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
</script>
@endpush
