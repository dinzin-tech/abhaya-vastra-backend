@extends('admin.layouts.app')
@push('meta')
<title>Change Password | {{ config('app.name') }}</title>
<meta content="Change Password" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush
@section('content')

<!-- end:: Header -->
<div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

    <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid mt-4">
        <div class="row">
            <div class="col-lg-12">
                <!--begin::Portlet-->
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                Change Password
                            </h3>
                        </div>
                        <div class="kt-portlet__head-toolbar">
                            <a href="javascript:history.back()" class="btn btn-clean kt-margin-r-10">
                                <i class="la la-arrow-left"></i>
                                <span class="kt-hidden-mobile">Back</span>
                            </a>
                            
                        </div>
                    </div>
                    <!--begin::Form-->
                    <form class="kt-form kt-form--label-right" action="{{route('admin.password.change.submit')}}" method="post"
                        onsubmit="return formSubmit(this)">
                        <div class="alert alert-success alert-dismissible" role="alert" id="msg" style="display:none;">
                            <div class="alert-text"></div>
                            <div class="alert-close">
                                <i class="flaticon2-cross kt-icon-sm" data-dismiss="alert"></i>
                            </div>
                        </div>
                        @csrf
                        <div class="kt-portlet__body">

                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label>Current Password:</label>
                                    <input type="password" class="form-control" name="current_pwd" placeholder="Current Password"
                                        >
                                    <div class="help-block"></div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label class="">New Password:</label>
                                    <input type="password" class="form-control" name="password" placeholder="New Password"
                                        >
                                    <div class="help-block"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label>Confirm Password:</label>
                                    <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password"
                                        >
                                    <div class="help-block"></div>
                                </div>
                                
                            </div>

                        </div>
                        <div class="kt-portlet__foot">
                            <div class="kt-form__actions">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <button type="submit" class="btn btn-primary saveBtn">Change Password</button>
                                        <button type="reset" class="btn btn-secondary">Reset</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Portlet-->


            </div>
        </div>
    </div>


    @stop