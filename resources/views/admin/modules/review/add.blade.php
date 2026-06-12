@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Review | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Review" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Review</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" 
                  action="{{ route('reviews.store') }}" 
                  method="post" 
                  enctype="multipart/form-data" 
                  onsubmit="return updateProfileimgs(this)">
                @csrf
                <input type="hidden" name="id" value="{{ optional($item)->id ?? '' }}">

                <div class="card__title-wrap mb-20">
                    <div class="text-end">
                        <a href="javascript:history.back()" class="btn btn-info">
                            <i class="la la-arrow-left"></i>
                            <span class="kt-hidden-mobile">Back</span>
                        </a>
                        <button type="button" id="editBtn" class="btn btn-primary" style="{{ $item ? '' : 'display:none;' }}" onclick="enableEdit(this);">
                            <span class="kt-hidden-mobile">Edit</span>
                        </button>
                    </div>
                </div>

                <!-- Reviewer Name -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="name">Reviewer Name</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="{{ optional($item)->name ?? '' }}" 
                                   {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="rating">Rating (1–5)</label>
                        </div>
                        <div class="form__input">
                            <input type="number" name="rating" id="rating" class="form-control" min="1" max="5"
                                   value="{{ optional($item)->rating ?? 5 }}" 
                                   {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>

                <!-- Review Text -->
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <div class="form__input-title">
                            <label for="review">Review</label>
                        </div>
                        <div class="form__input">
                            <textarea name="review" id="review" class="form-control" rows="5" {{ $item ? 'readonly' : '' }}>{{ optional($item)->review ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="image">Reviewer Image</label>
                        </div>
                        <div class="form__input">
                            <input type="file" name="image" id="image" class="form-control" accept="image/*" {{ $item ? 'disabled' : '' }}>
                            @if($item && $item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" alt="Reviewer Image" class="mt-3 rounded" width="120">
                            @endif
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
    var enableEdit = function(ele) {
        $('input, textarea').attr('readonly', false);
        $('select, input[type=file]').attr('disabled', false);
        $('.saveBtn').show();
        $(ele).addClass('invisible');
    }

    @if(!$item)
        enableEdit();
    @endif
</script>
@endpush
