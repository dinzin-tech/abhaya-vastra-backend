@extends('admin.layouts.app')
@push('meta')
<title>Admin Profile | {{ config('app.name') }}</title>
<meta content="Admin Profile" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush
@section('content')

<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-xxl-7">
            <div class="card__wrapper height-equal">
                <div class="employee__profile-single-box p-relative">
                    <div class="card__title-wrap d-flex align-items-center justify-content-between mb-15">
                        <h5 class="card__heading-title">Personal Information</h5>
                        <a class="edit-icon" href="{{route('admin.profile.update.form')}}">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                    </div>
                    <div class="profile-view d-flex flex-wrap justify-content-between align-items-start">
                        <div class="d-flex flex-wrap align-items-start gap-20">
                            <div class="profile-img-wrap">
                                <div class="profile-img">
                                    <a href="#"><img src="assets/images/avatar/avatar1.png" alt="User Image"></a>
                                </div>
                            </div>
                            <div class="profile-info">
                                <h3 class="user-name mb-15">{{auth()->user()->name}}</h3>
                                <h6 class="text-muted mb-5">{{auth()->user()->role->name ?? ''}}</h6>
                            </div>
                        </div>
                        <div class="personal-info-wrapper pr-20">
                            <ul class="personal-info">
                                <li>
                                    <div class="title">Phone:</div>
                                    <div class="text text-link-hover"><a href="tel:+18006427676"> {{auth()->user()->country_code ?? ''}}{{auth()->user()->mobile ?? ''}}</a></div>
                                </li>
                                <li>
                                    <div class="title">Email:</div>
                                    <div class="text text-link-hover"><a href="mailto:{{auth()->user()->email}}">{{auth()->user()->email}}</a></div>
                                </li>
                                <li>
                                    <div class="title">Birthday:</div>
                                    <div class="text">{{auth()->user()->dob ? date('jS M Y', strtotime(auth()->user()->dob)) : ''}}</div>
                                </li>
                                <li>
                                    <div class="title">Address:</div>
                                    <div class="text">{{auth()->user()->address}}</div>
                                </li>
                                <li>
                                    <div class="title">Gender:</div>
                                    <div class="text">{{auth()->user()->gender ?? ''}}</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       

</div>

@stop