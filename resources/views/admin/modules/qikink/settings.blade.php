@extends('admin.layouts.app')

@push('meta')
<title>Qikink POD Configuration | {{ config('app.name') }}</title>
<meta content="Qikink Integration Settings" name="description" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Qikink POD Integration</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xxl-8 col-xl-8 col-lg-8">
            <form class="card__wrapper p-30" action="{{ route('admin.qikink.settings.update') }}" method="POST">
                @csrf

                <h4 class="mb-20">Qikink Print on Demand (POD) Config</h4>

                <div class="from__input-box mb-20">
                    <div class="form__input-title">
                        <label for="qikink_client_id">Qikink Client ID <span>*</span></label>
                    </div>
                    <div class="form__input">
                        <input type="text" name="qikink_client_id" id="qikink_client_id" class="form-control" placeholder="Enter ClientId from Qikink custom API panel" value="{{ old('qikink_client_id', $settings['qikink_client_id'] ?? '') }}" required />
                    </div>
                </div>

                <div class="from__input-box mb-20">
                    <div class="form__input-title">
                        <label for="qikink_client_secret">Qikink Client Secret <span>*</span></label>
                    </div>
                    <div class="form__input">
                        <input type="password" name="qikink_client_secret" id="qikink_client_secret" class="form-control" placeholder="Enter client_secret from Qikink custom API panel" value="{{ old('qikink_client_secret', $settings['qikink_client_secret'] ?? '') }}" required />
                    </div>
                </div>

                <div class="from__input-box mb-20">
                    <div class="form__input-title">
                        <label for="qikink_sandbox_mode">Select API Mode <span>*</span></label>
                    </div>
                    <div class="form__input">
                        <select name="qikink_sandbox_mode" id="qikink_sandbox_mode" class="form-control">
                            <option value="1" {{ old('qikink_sandbox_mode', $settings['qikink_sandbox_mode'] ?? '1') === '1' ? 'selected' : '' }}>Sandbox Mode (Testing / https://sandbox.qikink.com)</option>
                            <option value="0" {{ old('qikink_sandbox_mode', $settings['qikink_sandbox_mode'] ?? '1') === '0' ? 'selected' : '' }}>Live Mode (Production / https://api.qikink.com)</option>
                        </select>
                    </div>
                </div>

                <h5 class="mt-30 mb-15 text-primary">Defaults & Fulfillment Behaviors</h5>

                <div class="from__input-box mb-20">
                    <div class="form__input-title">
                        <label for="qikink_default_print_type_id">Default Printing Method <span>*</span></label>
                    </div>
                    <div class="form__input">
                        <select name="qikink_default_print_type_id" id="qikink_default_print_type_id" class="form-control">
                            <option value="1" {{ old('qikink_default_print_type_id', $settings['qikink_default_print_type_id'] ?? '1') == '1' ? 'selected' : '' }}>DTG (Direct to Garment)</option>
                            <option value="17" {{ old('qikink_default_print_type_id', $settings['qikink_default_print_type_id'] ?? '1') == '17' ? 'selected' : '' }}>DTF (Direct to Film)</option>
                            <option value="3" {{ old('qikink_default_print_type_id', $settings['qikink_default_print_type_id'] ?? '1') == '3' ? 'selected' : '' }}>Embroidery</option>
                            <option value="2" {{ old('qikink_default_print_type_id', $settings['qikink_default_print_type_id'] ?? '1') == '2' ? 'selected' : '' }}>All-over Printed Products</option>
                            <option value="5" {{ old('qikink_default_print_type_id', $settings['qikink_default_print_type_id'] ?? '1') == '5' ? 'selected' : '' }}>Accessories</option>
                        </select>
                    </div>
                </div>

                <div class="from__input-box mb-20">
                    <div class="form__input-title">
                        <label for="qikink_default_shipping">Default Shipping Handler <span>*</span></label>
                    </div>
                    <div class="form__input">
                        <select name="qikink_default_shipping" id="qikink_default_shipping" class="form-control">
                            <option value="1" {{ old('qikink_default_shipping', $settings['qikink_default_shipping'] ?? '1') === '1' ? 'selected' : '' }}>Qikink Domestic Dropshipping (Recommended)</option>
                            <option value="0" {{ old('qikink_default_shipping', $settings['qikink_default_shipping'] ?? '1') === '0' ? 'selected' : '' }}>Self Shipping (Processed locally via primary Shiprocket)</option>
                        </select>
                    </div>
                </div>

                <div class="from__input-box mb-20">
                    <div class="form__input-title">
                        <label for="qikink_auto_push">Automatic Order Submission <span>*</span></label>
                    </div>
                    <div class="form__input">
                        <select name="qikink_auto_push" id="qikink_auto_push" class="form-control">
                            <option value="1" {{ old('qikink_auto_push', $settings['qikink_auto_push'] ?? '1') === '1' ? 'selected' : '' }}>Enabled (Auto-submit to Qikink when payment is completed)</option>
                            <option value="0" {{ old('qikink_auto_push', $settings['qikink_auto_push'] ?? '1') === '0' ? 'selected' : '' }}>Disabled (Manual review needed, admin pushes order manually)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-30">
                    <button type="submit" class="btn btn-primary">Save Settings & Verify Access</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
