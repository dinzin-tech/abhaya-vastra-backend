@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Banner | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Banner" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Banner</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('banner.store') }}" method="post" enctype="multipart/form-data" onsubmit="return updateProfileimgs(this)">



            
                @csrf
                <input type="hidden" name="id" value="{{ $item ? $item->id : '' }}">

                <div class="card__title-wrap mb-20">
                    <div class="text-end">
                        <a href="javascript:history.back()" class="btn btn-info kt-margin-r-10">
                            <i class="la la-arrow-left"></i>
                            <span class="kt-hidden-mobile">Back</span>
                        </a>
                        <button type="button" id="editBtn" class="btn btn-primary" style="{{ $item ? '' : 'display:none;' }}" onclick="enableEdit(this);">
                            <span class="kt-hidden-mobile">Edit</span>
                        </button>
                    </div>
                </div>

                <!-- Banner Image Upload -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="image">Banner Image<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewBanner(this)" {{ $item ? 'disabled' : '' }} />

                                <!-- Existing Image -->
                                <div class="mt-2">
                                    <img id="bannerPreview" src="{{ isset($item->image) ? asset('storage/' . $item->image) : '' }}" alt="Banner Preview" style="height:100px; {{ isset($item->image) ? '' : 'display:none;' }}">
                                </div>

                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-auto saveBtn mt-5" style="{{ $item ? 'display:none;' : '' }}" type="submit">
                    {{ $item ? 'Update' : 'Add' }}
                </button>
            </form>
        </div>
    </div>
</div>
@stop

@push('appendJs')

<script src="{{asset('assets/js/plugins/flatpickr.js')}}"></script>
<script src="{{asset('assets/js/plugins/select2.full.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
<script src="{{asset('assets/js/post-jobs.js')}}"></script>
<script src="{{asset('assets/js/save-file.js') }}" type="text/javascript" charset="utf-8"></script>
<script>
  

     function previewBanner(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('bannerPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Automatically enable edit if adding a new item
   var enableEdit = function(ele) {
        $('#image').prop('disabled', false);
        $('.saveBtn').show();
        $(ele).hide();
    }

    @if(!$item)
        enableEdit();
    @endif
    </script>
@endpush
