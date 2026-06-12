@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Short Video | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Short Video" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Short Video</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('video.store') }}" method="post" enctype="multipart/form-data" onsubmit="return updateProfileimgs(this)">
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

                <!-- Title Field -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="title">Title<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="text" id="title" name="title" class="form-control" placeholder="Enter short video title"
                                    value="{{ old('title', $item->title ?? '') }}" {{ $item ? 'disabled' : '' }} />
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Short Video Upload -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="video">Short Video<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="file" id="video" name="video" class="form-control" accept="video/mp4,video/webm,video/ogg" onchange="previewVideo(this)" {{ $item ? 'disabled' : '' }} />

                                <!-- Existing Video Preview -->
                                <div class="mt-3">
                                    <video id="videoPreview" controls style="height: 200px; {{ isset($item->video) ? '' : 'display:none;' }}">
                                        @if(isset($item->video))
                                            <source src="{{ asset('storage/' . $item->video) }}" type="video/mp4">
                                        @endif
                                        Your browser does not support the video tag.
                                    </video>
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
<script src="{{ asset('assets/js/plugins/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/plugins/select2.full.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
<script src="{{ asset('assets/js/post-jobs.js') }}"></script>
<script src="{{ asset('assets/js/save-file.js') }}" type="text/javascript" charset="utf-8"></script>
<script>
    // ✅ Video preview
    function previewVideo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const preview = document.getElementById('videoPreview');
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.style.display = 'block';
        }
    }

    // ✅ Enable Edit Mode
    var enableEdit = function(ele) {
        $('#title, #video').prop('disabled', false);
        $('.saveBtn').show();
        $(ele).hide();
    }

    @if(!$item)
        enableEdit();
    @endif
</script>
@endpush
