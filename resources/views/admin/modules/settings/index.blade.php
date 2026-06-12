@extends('admin.layouts.app')

@push('meta')
<title>General Settings | {{ config('app.name') }}</title>
<meta content="General Settings" name="description" />
<meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">General Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">
            <form class="card__wrapper" action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Basic Info -->
                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-4">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="site_name">Website Name <span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="text" id="site_name" name="site_name" class="form-control"
                                    value="{{ old('site_name', $settings['site_name'] ?? '') }}" required />
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="email">Support Email</label></div>
                            <div class="form__input">
                                <input type="email" id="email" name="email" class="form-control"
                                    value="{{ old('email', $settings['email'] ?? '') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="currency">Currency</label></div>
                            <div class="form__input">
                                <input type="text" id="currency" name="currency" class="form-control"
                                    value="{{ old('currency', $settings['currency'] ?? 'USD') }}" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descriptions -->
                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="short_description">Short Description</label></div>
                            <div class="form__input">
                                <textarea name="short_description" id="short_description" class="form-control" rows="4">{{ old('short_description', $settings['short_description'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="long_description">Long Description</label></div>
                            <div class="form__input">
                                <textarea name="long_description" id="long_description" class="form-control" rows="4">{{ old('long_description', $settings['long_description'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo & Favicon -->
                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="logo">Logo</label></div>
                            <div class="form__input">
                                <input type="file" name="logo" id="logo" class="form-control" accept="image/*"
                                    onchange="previewImage(this, 'logoPreview')" />
                                <div class="mt-2">
                                    @php
                                    $logoPath = $settings['logo'] ?? null;
                                    $logoUrl = $logoPath ? asset('storage/' . $logoPath) : null;
                                    @endphp
                                    <img id="logoPreview"
                                        src="{{ $logoUrl ?? '' }}"
                                        alt="Logo Preview"
                                        style="width:150px;height:150px;object-fit:contain;border:1px solid #ccc;{{ !$logoUrl ? 'display:none;' : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="from__input-box">
                            <div class="form__input-title"><label for="favicon">Favicon</label></div>
                            <div class="form__input">
                                <input type="file" name="favicon" id="favicon" class="form-control" accept="image/*"
                                    onchange="previewImage(this, 'faviconPreview')" />
                                <div class="mt-2">
                                    @php
                                    $faviconPath = $settings['favicon'] ?? null;
                                    $faviconUrl = $faviconPath ? asset('storage/' . $faviconPath) : null;
                                    @endphp
                                    <img id="faviconPreview"
                                        src="{{ $faviconUrl ?? '' }}"
                                        alt="Favicon Preview"
                                        style="width:80px;height:80px;object-fit:contain;border:1px solid #ccc;{{ !$faviconUrl ? 'display:none;' : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Break -->
                <hr class="my-4">

                <!-- Minimum Order Value Points for Purchase -->
                <div class="row gx-0 g-20 gy-20 mt-3">
                    <div class="col-lg-4">
                        <div class="from__input-box">
                            <div class="form__input-title">
                                <label for="min_order_value">Min Order Value <span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="number" id="min_order_value" name="min_order_value" class="form-control"
                                    value="{{ old('min_order_value', $settings['min_order_value'] ?? '') }}" min="0" step="1" />
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">

                        <div class="row align-items-end mt-3">
                            <div class="col-lg-5 col-md-5">
                                <div class="from__input-box">
                                    <div class="form__input-title">
                                        <label for="reward_base_amount">Reward Base Amount (₹)</label>
                                    </div>
                                    <div class="form__input">
                                        <input type="number" id="reward_base_amount" name="reward_base_amount" class="form-control"
                                            value="{{ old('reward_base_amount', $settings['reward_base_amount'] ?? '') }}" min="0" step="1" placeholder="e.g. 100" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-2 col-md-2 d-flex align-items-center justify-content-center">
                                <span class="fw-bold fs-5">=</span>
                            </div>

                            <div class="col-lg-5 col-md-5">
                                <div class="from__input-box">
                                    <div class="form__input-title">
                                        <label for="reward_points_per_base">Reward Points</label>
                                    </div>
                                    <div class="form__input">
                                        <input type="number" id="reward_points_per_base" name="reward_points_per_base" class="form-control"
                                            value="{{ old('reward_points_per_base', $settings['reward_points_per_base'] ?? '') }}" min="0" step="1" placeholder="e.g. 1" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-4">

                    </div>
                </div>

                <!-- Submit -->
                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('appendJs')
<script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }
</script>
@endpush