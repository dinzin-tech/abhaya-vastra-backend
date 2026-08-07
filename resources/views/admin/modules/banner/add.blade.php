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

                <div class="row g-4 mt-2">
                    <!-- Desktop Banner Image Upload -->
                    <div class="col-md-6">
                        <div class="from__input-box border p-3 rounded bg-light">
                            <div class="form__input-title mb-2">
                                <label for="image" class="fw-bold">🖥️ Desktop Banner Image <span>*</span></label>
                                <small class="text-muted d-block">Recommended size: <strong>1920 × 800 px</strong> (Landscape 16:9)</small>
                            </div>
                            <div class="form__input">
                                <input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewBanner(this, 'bannerPreview')" {{ $item ? 'disabled' : '' }} />

                                <!-- Existing Image -->
                                @php
                                    $desktopSrc = isset($item->image) ? (\Illuminate\Support\Str::startsWith($item->image, ['http://', 'https://']) ? $item->image : asset('storage/' . $item->image)) : '';
                                @endphp
                                <div class="mt-3">
                                    <img id="bannerPreview" src="{{ $desktopSrc }}" alt="Desktop Banner Preview" style="max-height:160px; width:auto; border-radius:6px; {{ isset($item->image) ? '' : 'display:none;' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Banner Image Upload -->
                    <div class="col-md-6">
                        <div class="from__input-box border p-3 rounded bg-light">
                            <div class="form__input-title mb-2">
                                <label for="mobile_image" class="fw-bold">📱 Mobile Banner Image <span class="text-muted">(Optional)</span></label>
                                <small class="text-muted d-block">Recommended size: <strong>800 × 1200 px</strong> (Portrait 4:5 or 9:16)</small>
                            </div>
                            <div class="form__input">
                                <input type="file" id="mobile_image" name="mobile_image" class="form-control" accept="image/*" onchange="previewBanner(this, 'mobileBannerPreview')" {{ $item ? 'disabled' : '' }} />

                                <!-- Existing Image -->
                                @php
                                    $mobileSrc = isset($item->mobile_image) ? (\Illuminate\Support\Str::startsWith($item->mobile_image, ['http://', 'https://']) ? $item->mobile_image : asset('storage/' . $item->mobile_image)) : $desktopSrc;
                                @endphp
                                <div class="mt-3">
                                    <img id="mobileBannerPreview" src="{{ $mobileSrc }}" alt="Mobile Banner Preview" style="max-height:160px; width:auto; border-radius:6px; {{ isset($item->mobile_image) || isset($item->image) ? '' : 'display:none;' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-auto saveBtn mt-4" style="{{ $item ? 'display:none;' : '' }}" type="submit">
                    {{ $item ? 'Update' : 'Add' }} Banner
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
    function previewBanner(input, targetId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById(targetId);
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    var enableEdit = function(ele) {
        $('#image, #mobile_image').prop('disabled', false);
        $('.saveBtn').show();
        $(ele).hide();
    }

    @if(!$item)
        enableEdit();
    @endif
</script>
@endpush
