@extends('admin.layouts.app')

@push('meta')
<title>{{$item ? 'Update' : 'Add'}} Category | {{ config('app.name') }}</title>
<meta content="{{$item ? 'Update' : 'Add'}} Category" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{$item ? 'Update' : 'Add'}} Category</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{route('categories.store')}}" method="post" enctype="multipart/form-data"  onsubmit="return updateProfileimgs(this)">
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

                <!-- Name Input -->
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="name">Category Name<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="text" id="name" name="name" class="form-control" value="{{ $item->name ?? '' }}" {{$item ? 'readonly' : ''}} />
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gender Select -->
                <!-- <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-12">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="gender">Gender<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <select id="gender" name="gender" class="form-control" {{$item ? 'disabled' : ''}}>
                                    <option value="male" {{$item && $item->gender == 'male' ? 'selected' : ''}}>Male</option>
                                    <option value="female" {{$item && $item->gender == 'female' ? 'selected' : ''}}>Female</option>
                                    <option value="unisex" {{$item && $item->gender == 'unisex' ? 'selected' : ''}}>Unisex</option>
                                </select>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center mt-3">
                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="main_image">Main Image</label>
                            </div>
                            <div class="form__input">
                                <input type="file" name="main_image" id="main_image" class="form-control" accept="image/*" onchange="previewImage(this,'mainPreview')" {{$item ? '' : 'required'}} />
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
                                <input type="file" name="zoomed_image" id="zoomed_image" class="form-control" accept="image/*" onchange="previewImage(this,'zoomPreview')" {{$item ? '' : 'required'}} />
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
<script>
   var enableEdit = function (ele) {
        $('input').attr('readonly', false);
        $('select').attr('disabled', false);
        $('.saveBtn').show();
        $(ele).addClass('invisible');
    }

   
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

    // Automatically enable edit if adding a new item
    @if(!$item)
        enableEdit();
    @endif
</script>
@endpush