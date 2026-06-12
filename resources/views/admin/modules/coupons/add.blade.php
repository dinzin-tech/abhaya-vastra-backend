@extends('admin.layouts.app')

@push('meta')
<title>{{ $item ? 'Update' : 'Add' }} Coupon | {{ config('app.name') }}</title>
<meta content="{{ $item ? 'Update' : 'Add' }} Coupon" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Coupon</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('coupons.store') }}" method="post" onsubmit="return updateProfiles(this)">
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

                <!-- Coupon Code / Type -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="code">Coupon Code</label>
                        </div>
                        <div class="form__input">
                            <input type="text" name="code" id="code" class="form-control" value="{{ optional($item)->code ?? strtoupper(\Illuminate\Support\Str::random(8)) }}" {{ $item ? 'readonly' : '' }} required>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="type">Type</label>
                        </div>
                        <div class="form__input">
                            <select id="type" name="type" class="form-control" {{ $item ? 'disabled' : '' }} required>
                                <option value="percentage" {{ (optional($item)->type ?? '') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed" {{ (optional($item)->type ?? '') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Value / Min Cart Amount -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="value">Value</label>
                        </div>
                        <div class="form__input">
                            <input type="number" step="0.01" name="value" id="value" class="form-control" value="{{ optional($item)->value ?? '' }}" {{ $item ? 'readonly' : '' }} required>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="min_cart_amount">Min Cart Amount</label>
                        </div>
                        <div class="form__input">
                            <input type="number" step="0.01" name="min_cart_amount" id="min_cart_amount" class="form-control" value="{{ optional($item)->min_cart_amount ?? '' }}" {{ $item ? 'readonly' : '' }} required>
                        </div>
                    </div>
                </div>

                <!-- Usage Limit / Expires At -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="usage_limit">Usage Limit</label>
                        </div>
                        <div class="form__input">
                            <input type="number" name="usage_limit" id="usage_limit" class="form-control" value="{{ optional($item)->usage_limit ?? '' }}" {{ $item ? 'readonly' : '' }}>
                            <small class="text-muted">Leave empty for unlimited</small>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form__input-title">
                            <label for="expires_at">Expires At</label>
                        </div>
                        <div class="form__input">
                            <input type="date" name="expires_at" id="expires_at" class="form-control" value="{{ optional($item)->expires_at ? \Carbon\Carbon::parse($item->expires_at)->format('Y-m-d') : '' }}" {{ $item ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" {{ (optional($item)->status ?? 1) ? 'checked' : '' }} {{ $item ? 'disabled' : '' }}>
                            <label class="form-check-label" for="status">Active</label>
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
        $('#status').attr('disabled', false);
        $('.saveBtn').show();
        $(ele).addClass('invisible');
    }

    @if(!$item)
        enableEdit();
    @endif
</script>
@endpush