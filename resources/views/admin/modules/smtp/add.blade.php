@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} SMTP Settings | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} SMTP Settings" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} SMTP Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('smtp.store') }}" method="post" onsubmit="return updateProfiles(this)">
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

                <!-- Mailer / Encryption -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="mailer">Mailer</label>
                        </div>
                        <div class="form__input">
                            <select name="mailer" id="mailer" class="form-control" {{ $item ? 'disabled' : '' }}>
                                <option value="smtp" {{ (optional($item)->mailer ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ (optional($item)->mailer ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="mailgun" {{ (optional($item)->mailer ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="ses" {{ (optional($item)->mailer ?? '') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="encryption">Encryption</label>
                        </div>
                        <div class="form__input">
                            <select id="encryption" name="encryption" class="form-control" {{ $item ? 'disabled' : '' }}>
                                <option value="tls" {{ (optional($item)->encryption ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ (optional($item)->encryption ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ (optional($item)->encryption ?? '') == 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Host / Port -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="host">SMTP Host</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="host" id="host" class="form-control" value="{{ optional($item)->host ?? '' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="port">SMTP Port</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="port" id="port" class="form-control" value="{{ optional($item)->port ?? '' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>

                <!-- Username / Password -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="username">SMTP Username</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="username" id="username" class="form-control" value="{{ optional($item)->username ?? '' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="password">SMTP Password</label>
                        </div>
                        <div class="form__input">
                            <input type="password" name="password" id="password" class="form-control" value="{{ optional($item)->password ?? '' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>

                <!-- From Address / Name -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="from_address">From Address</label>
                        </div>
                        <div class="form__input">
                            <input type="email" name="from_address" id="from_address" class="form-control" value="{{ optional($item)->from_address ?? '' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="from_name">From Name</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="from_name" id="from_name" class="form-control" value="{{ optional($item)->from_name ?? '' }}" {{ $item ? 'readonly' : '' }}>
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
