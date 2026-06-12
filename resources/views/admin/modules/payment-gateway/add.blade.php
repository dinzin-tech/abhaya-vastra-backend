@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Payment Gateway | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Payment Gateway" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Payment Gateway</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('payment-gateway.store') }}" method="post" onsubmit="return updateProfiles(this)">
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

                <!-- Gateway Name / Currency -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="gateway_name">Gateway Name</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="gateway_name" id="gateway_name" class="form-control"
                                   value="{{ optional($item)->gateway_name ?? 'Razorpay' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="currency">Currency</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="currency" id="currency" class="form-control"
                                   value="{{ optional($item)->currency ?? 'INR' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>

                <!-- API Key / Secret -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="api_key">API Key</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="api_key" id="api_key" class="form-control"
                                   value="{{ optional($item)->api_key ?? '' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="api_secret">API Secret</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="api_secret" id="api_secret" class="form-control"
                                   value="{{ optional($item)->api_secret ?? '' }}" {{ $item ? 'readonly' : '' }}>
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
        $('input').attr('readonly', false);
        $('select').attr('disabled', false);
        $('.saveBtn').show();
        $(ele).addClass('invisible');
    }

    @if(!$item)
        enableEdit();
    @endif
</script>
@endpush
