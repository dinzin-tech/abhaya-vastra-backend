@extends('admin.layouts.app')
@push('meta')
<title>Admin Profile | {{ config('app.name') }}</title>
<meta content="Admin Profile" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush
@push('appendCss')
<link rel="stylesheet" href="{{asset('assets/css/plugins/flatpickr.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/plugins/select2.min.css')}}">
@endpush
@section('content')

<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Form Style</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{route('admin.profile.update')}}" method="post"
            onsubmit="return updateProfile(this)">
                <div class="alert alert-success alert-dismissible" role="alert" id="msg" style="display:none;">
                    <div class="alert-text"></div>
                    <div class="alert-close">
                        <i class="flaticon2-cross kt-icon-sm" data-dismiss="alert"></i>
                    </div>
                </div>
                @csrf
                <input type="hidden" name="image" id="user_image" value="{{Auth::user()->profile_image}}">
                <input type="hidden" name="id" id="user_id" value="false">
                <div class="card__title-wrap mb-20">
                    <div class="text-end">
                        <a href="javascript:history.back()" class="btn btn-info kt-margin-r-10">
                            <i class="la la-arrow-left"></i>
                            <span class="kt-hidden-mobile">Back</span>
                        </a>
                        <button type="button" id="editBtn" class="btn btn-primary" onclick="enableEdit(this);">
                            <span class="kt-hidden-mobile">Edit</span>
                        </button>
                    </div>
                </div>
                <div class="row gx-0 g-20 gy-20 align-items-center justify-content-center" >
                    <div class="col-md-12">
                        <div class="employee__profile-chnage">
                            <div class="employee__profile-edit upload_image_profile" style="display:none;" id="updateProfilePic">
                                <label for="imageUpload" onclick="$(this).parent('.dz-clickable').click()"></label>
                            </div>
                            <div class="employee__profile-preview">
                                <div class="employee__profile-preview-box" id="imagePreview" style="background-image: url({{Auth::user()->default_profile_image}});">
                                </div>
                            </div>
                            <div class="help-block"></div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="text">Name<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input class="form-control" name="name" type="text" placeholder="Name" value="{{Auth::user()->name}}" readonly>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="text">Email Id<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input class="form-control" name="email" type="text" placeholder="Email Id" value="{{Auth::user()->email}}" disabled>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="text">Country Code<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <select class="form-control country-code-select2" name="country_code" disabled>
                                    @foreach(collect($country_codes)->sortBy('name') as $code)
                                    <option value="{{$code->dial_code}}"
                                        {{Auth::user()->country_code == $code->dial_code ? 'selected' : ''}}>
                                        {{$code->dial_code.' '.$code->name}}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="text">Mobile No.<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input class="form-control" type="text" name="mobile" placeholder="Mobile No."
                                value="{{Auth::user()->mobile}}" readonly>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="text">Date of Birth<span>*</span></label>
                            </div>
                            <div class="form__input">
                            <input id="dobDate" type="text" class="form-control" name="dob" placeholder="Date of Birth."
                            value="{{\Auth::user()->dob ? date('Y-m-d', strtotime(\Auth::user()->dob)) : ''}}" readonly>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="text">Gender<span>*</span></label>
                            </div>
                            <div class="form__input">
                                <select class="form-select" name="gender" disabled>
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{auth()->user()->gender == 'Male' ? 'selected' : ''}}>Male</option>
                                    <option value="Female" {{auth()->user()->gender == 'Female' ? 'selected' : ''}}>Female</option>
                                    <option value="Other" {{auth()->user()->gender == 'Other' ? 'selected' : ''}}>Other</option>
                                </select>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="text">Address<span>*</span></label>
                            </div>
                            <div class="form__input">
                            <input type="text" class="form-control" name="address" placeholder="Address"
                            value="{{\Auth::user()->address}}" readonly>
                                <div class="help-block"></div>
                            </div>
                        </div>
                    </div>

                <button class="btn btn-primary w-auto saveBtn" style="display:none;" type="submit"><i class="fa fa-spinner fa-spin" style="display:none;"></i> Update</button>
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
        var uploadUrl = "{{ route('admin.profile.upload') }}";
        var imgBaseUrl = "{{ asset('storage/images/users/').'/' }}";
    </script>
    <script>
        var enableEdit = function (ele) {
            $('input').attr('readonly', false);
            $('select').attr('disabled', false);
            $('.saveBtn').show();
            $('#updateProfilePic').show();
            $(ele).addClass('invisible');
            $('#updateProfilePic').addClass('upload_image_profile');
            initDropforUpdate('_profile', false, false);
            $("#dobDate").flatpickr();
            $(".country-code-select2").select2();
        }
    </script>
@endpush