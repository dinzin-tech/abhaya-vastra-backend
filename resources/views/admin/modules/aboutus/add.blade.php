@extends('admin.layouts.app')

@push('meta')
<title>{{$item ? 'Update' : 'Add'}} About Us | {{ config('app.name') }}</title>
<meta content="{{$item ? 'Update' : 'Add'}} About Us" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{$item ? 'Update' : 'Add'}} About Us</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{route('about-us.store')}}" method="post" onsubmit="return updateProfile(this)">
                @csrf
                <input type="hidden" name="id" value="{{$item ? $item->id : ''}}">

                <div class="card__title-wrap mb-20">
                    <div class="text-end">
                        <a href="javascript:history.back()" class="btn btn-info kt-margin-r-10">
                            <i class="la la-arrow-left"></i>
                            <span class="kt-hidden-mobile">Back</span>
                        </a>
                        <button type="button" id="editBtn" class="btn btn-primary" style="{{$item ? '' : 'display:none;'}}" onclick="enableEdit(this);">
                            <span class="kt-hidden-mobile">Edit</span>
                        </button>
                    </div>
                </div>

                <!-- Title Input -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="title">Title<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="text" id="title" name="title" class="form-control" value="{{ $item->title ?? '' }}" {{$item ? 'readonly' : ''}} />
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description (CKEditor) -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="description">Description<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <textarea id="description" name="description" class="form-control" rows="10" {{$item ? 'readonly' : ''}}>
                                    {!! $item->description ?? '' !!}
                                </textarea>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-auto saveBtn mt-5" style="{{$item ? 'display:none;' : ''}}" type="submit">
                    <i class="fa fa-spinner fa-spin" style="display:none;"></i> {{$item ? 'Update' : 'Add'}}
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
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
   var enableEdit = function (ele) {
        $('input').attr('readonly', false);
        $('textarea').attr('readonly', false);
        $('.saveBtn').show();
        $(ele).addClass('invisible');

        if(CKEDITOR.instances.description) {
            CKEDITOR.instances.description.setReadOnly(false);
        }
    }

    // Automatically enable edit if adding a new item
    @if(!$item)
        enableEdit();
    @endif
    </script>


<script>
    // Initialize CKEditor for description
    CKEDITOR.replace('description');

    // Set readonly if editing existing item
    @if($item)
        CKEDITOR.instances.description.on('instanceReady', function() {
            CKEDITOR.instances.description.setReadOnly(true);
        });
    @endif

    // Enable editing
 

    // Form submission function
   
</script>
@endpush
