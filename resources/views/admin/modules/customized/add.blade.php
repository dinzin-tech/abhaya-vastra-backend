@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Customize Product | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Customize Product" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Customize Product</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('customized.store') }}" method="post"
                  enctype="multipart/form-data" onsubmit="return updateProfileimg(this)">
                @csrf
                <input type="hidden" name="id" value="{{ $item->id ?? '' }}">
                <input type="hidden" name="removed_images" id="removed_images" value="[]">

                <div class="card__title-wrap mb-20">
                    <div class="text-end">
                        <a href="{{ route('customized.index') }}" class="btn btn-info">
                            <i class="la la-arrow-left"></i> Back
                        </a>
                        <button type="button" id="editBtn" class="btn btn-primary"
                                style="{{ $item ? '' : 'display:none;' }}" onclick="enableEdit(this)">
                            Edit
                        </button>
                    </div>
                </div>

                <!-- Title -->
                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="title">Title <span>*</span></label></div>
                            <div class="form__input">
                                <input type="text" id="title" name="title" class="form-control"
                                       value="{{ $item->title ?? '' }}"  required>
                            </div>
                        </div>
                    </div>
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
                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="description">Description</label></div>
                            <div class="form__input">
                                <textarea name="description" id="description" class="form-control"
                                          rows="4" {{ $item ? 'readonly' : '' }}>{{ $item->description ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="images">Images (multiple)</label></div>
                            <div class="form__input">
                                <input type="file" id="images" name="images[]" multiple accept="image/*"
                                       class="form-control" onchange="previewCustomizeImages(this)"
                                       {{ $item ? 'disabled' : '' }}>
                                <div id="customizeImagesPreview" class="mt-3 d-flex flex-wrap gap-2">
                                    @if($item && $item->images)
                                        @foreach(json_decode($item->images) as $img)
                                            <div class="customize-img-wrapper position-relative me-2 mb-2" style="width:100px;height:100px;">
                                                <img src="{{ asset('storage/'.$img) }}"
                                                     style="width:100%;height:100%;object-fit:cover;border:1px solid #ccc;">
                                                <button type="button"
                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 d-none"
                                                        onclick="removeOldCustomizeImage(this,'{{ $img }}')">×</button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-auto saveBtn mt-5" type="submit"
                        style="{{ $item ? 'display:none;' : '' }}">
                    {{ $item ? 'Update' : 'Add' }}
                </button>
            </form>
        </div>
    </div>
</div>
@stop

@push('appendJs')




<script src="{{ asset('assets/js/vendor/jquery-3.7.0.js') }}"></script>

<script src="{{ asset('assets/js/plugins/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/plugins/select2.full.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
<script src="{{ asset('assets/js/post-jobs.js') }}"></script>
<script src="{{ asset('assets/js/save-file.js') }}" type="text/javascript" charset="utf-8"></script>


<script>
let removedOldImages = [];
let newImages = [];

var enableEdit = function(ele){
    $('input, textarea, select').attr('readonly', false).attr('disabled', false);
    $('.saveBtn').show();
    $(ele).hide();
    document.querySelectorAll('.customize-img-wrapper button').forEach(btn=>{
        btn.classList.remove('d-none');
    });
}

@if(!$item)
enableEdit();
@endif

function previewCustomizeImages(input) {
    const previewContainer = document.getElementById('customizeImagesPreview');
    const filesArray = Array.from(input.files);

    filesArray.forEach(file => {
        newImages.push(file);
        const reader = new FileReader();
        reader.onload = e => {
            const wrapper = document.createElement('div');
            wrapper.classList.add('customize-img-wrapper','position-relative','me-2','mb-2');
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

    input.value = ''; // clear input to allow re-selection
}

function removeOldCustomizeImage(el, imgPath){
    removedOldImages.push(imgPath);
    el.parentElement.remove();
    document.getElementById('removed_images').value = JSON.stringify(removedOldImages);
}

function submitCustomizeForm(form){
    let formData = new FormData(form);
    newImages.forEach(file => formData.append('images[]', file));

    let totalImages = document.querySelectorAll('#customizeImagesPreview .customize-img-wrapper').length;
    if(totalImages < 1){
        alert('Please select at least one image');
        return false;
    }

    axios.post(form.action, formData, {
        headers: {'Content-Type':'multipart/form-data'}
    }).then(res=>{
        if(res.data.redirect){
            window.location.href = res.data.redirect;
        } else {
            alert(res.data.message);
        }
    }).catch(err=>{
        alert(err.response?.data?.message || 'Something went wrong');
    });

    return false;
}

 $('#title').on('input', function(){
        let slug = $(this).val().toLowerCase().replace(/\s+/g,'-').replace(/[^\w-]+/g,'');
        $('#slug').val(slug);
    });
</script>
@endpush