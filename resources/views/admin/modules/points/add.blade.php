@extends('admin.layouts.app')

@push('meta')
    <title>{{ $item ? 'Update' : 'Add' }} Points | {{ config('app.name') }}</title>
    <meta content="{{ $item ? 'Update' : 'Add' }} Points" name="description" />
    <meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item ? 'Update' : 'Add' }} Points</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12 col-xl-12 col-lg-12">

            <!-- ======= Add / Edit Points Form ======= -->
            <form action="{{ $item ? route('points.update', $item->id) : route('points.store') }}" method="POST">
                @csrf
                @if($item)
                    @method('PUT')
                @endif

                <div class="row gx-0 g-20 gy-20 mt-3">
                    <!-- Min Amount -->
                    <div class="col-lg-6">
                        <div class="form__input-box">
                            <div class="form__input-title">
                                <label for="min_amount">Min Amount <span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="number" step="0.01" id="min_amount" name="min_amount" class="form-control"
                                    value="{{ old('min_amount', $item->min_amount ?? '') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Max Amount -->
                    <div class="col-lg-6">
                        <div class="form__input-box">
                            <div class="form__input-title">
                                <label for="max_amount">Max Amount</label>
                            </div>
                            <div class="form__input">
                                <input type="number" step="0.01" id="max_amount" name="max_amount" class="form-control"
                                    value="{{ old('max_amount', $item->max_amount ?? '') }}">
                                <small class="text-muted">Leave empty for unlimited</small>
                            </div>
                        </div>
                    </div>

                    <!-- Points -->
                    <div class="col-lg-6">
                        <div class="form__input-box">
                            <div class="form__input-title">
                                <label for="points">Points <span>*</span></label>
                            </div>
                            <div class="form__input">
                                <input type="number" id="points" name="points" class="form-control"
                                    value="{{ old('points', $item->points ?? '') }}" required>
                            </div>
                        </div>
                    </div>


 <!-- Coin Value -->
<div class="col-lg-6">
    <div class="form__input-box">
        <div class="form__input-title">
            <label for="coin_value">Coin Value (₹ per Coin) <span>*</span></label>
        </div>
        <div class="form__input">
            <input type="number" step="0.01" id="coin_value" name="coin_value" class="form-control"
                value="{{ old('coin_value', $item->coin_value ?? 1) }}" required>
            <small class="text-muted">Example: 1 coin = ₹1</small>
        </div>
    </div>
</div>


                    <!-- Active Status -->
                    <div class="col-lg-6">
                        <div class="form__input-box my-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                                    {{ old('status', $item->status ?? 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>

                </div>

                <button class="btn btn-primary w-auto my-5" type="submit">
                    {{ $item ? 'Update' : 'Add' }} Points
                </button>
            </form>

        </div>
    </div>
</div>
@stop

@push('appendJs')
    <script src="{{ asset('assets/js/plugins/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/select2.full.min.js') }}"></script>
@endpush
