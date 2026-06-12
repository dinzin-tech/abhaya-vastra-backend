@extends('admin.layouts.app')

@push('meta')
    <title>Reward Points Settings | {{ config('app.name') }}</title>
    <meta content="Reward Points Settings" name="description" />
    <meta content="{{ config('app.name') }}" name="author" />
@endpush

@section('content')
<div class="app__slide-wrapper">
    <div class="breadcrumb__area">
        <div class="breadcrumb__wrapper mb-25">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reward Points Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-8 col-xl-10 col-lg-12">
            <div class="card__wrapper">
                <div class="card-header">
                    <h4>Configure Reward Points System</h4>
                    <p class="text-muted">Set up how customers earn loyalty points on their orders</p>
                </div>
                
                <div class="card-body">
                    <!-- Explanation Box -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="fa fa-info-circle"></i> How it works:</h6>
                        <p class="mb-2">Customers earn points based on their order value:</p>
                        <ul class="mb-0">
                            <li><strong>Min Order Value:</strong> Minimum order amount required to earn points</li>
                            <li><strong>Reward Base Amount:</strong> The amount that determines points calculation</li>
                            <li><strong>Reward Points:</strong> Points given per base amount</li>
                            <li><strong>Points Value:</strong> Value of 1 point in rupees (₹)</li>
                        </ul>
                        <p class="mt-2 mb-0">
                            <strong>Example:</strong> Min Order = ₹100, Base Amount = ₹100, Points = 1<br>
                            For a ₹500 order → Customer gets 5 points (₹500 ÷ ₹100 × 1 = 5 points)
                        </p>
                    </div>

                    <form action="{{ route('reward-settings.store') }}" method="POST">
                        @csrf

                        <div class="row gx-0 g-20 gy-20">
                            <!-- Min Order Value -->
                            <div class="col-lg-6">
                                <div class="form__input-box">
                                    <div class="form__input-title">
                                        <label for="min_order_value">Min Order Value (₹) <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form__input">
                                        <input type="number" step="0.01" id="min_order_value" name="min_order_value" 
                                            class="form-control @error('min_order_value') is-invalid @enderror"
                                            value="{{ old('min_order_value', $setting->min_order_value ?? 100) }}" required>
                                        <small class="text-muted">Minimum order amount to earn points</small>
                                        @error('min_order_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Reward Base Amount -->
                            <div class="col-lg-6">
                                <div class="form__input-box">
                                    <div class="form__input-title">
                                        <label for="reward_base_amount">Reward Base Amount (₹) <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form__input">
                                        <input type="number" step="0.01" id="reward_base_amount" name="reward_base_amount" 
                                            class="form-control @error('reward_base_amount') is-invalid @enderror"
                                            value="{{ old('reward_base_amount', $setting->reward_base_amount ?? 100) }}" required>
                                        <small class="text-muted">Base amount for points calculation</small>
                                        @error('reward_base_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Reward Points -->
                            <div class="col-lg-6">
                                <div class="form__input-box">
                                    <div class="form__input-title">
                                        <label for="reward_points">Reward Points <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form__input">
                                        <input type="number" id="reward_points" name="reward_points" 
                                            class="form-control @error('reward_points') is-invalid @enderror"
                                            value="{{ old('reward_points', $setting->reward_points ?? 1) }}" required>
                                        <small class="text-muted">Points awarded per base amount</small>
                                        @error('reward_points')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Points Value (in Rupees) -->
                            <div class="col-lg-6">
                                <div class="form__input-box">
                                    <div class="form__input-title">
                                        <label for="points_value">Points Value (₹ per Point) <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="form__input">
                                        <input type="number" step="0.01" id="points_value" name="points_value" 
                                            class="form-control @error('points_value') is-invalid @enderror"
                                            value="{{ old('points_value', $setting->points_value ?? 1) }}" required>
                                        <small class="text-muted">Value of 1 loyalty point when used (e.g., 1 point = ₹1)</small>
                                        @error('points_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-lg-12">
                                <div class="form__input-box my-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                                            {{ old('status', $setting->status ?? 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            <strong>Enable Reward Points System</strong>
                                            <small class="d-block text-muted">Turn on to allow customers to earn loyalty points</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Calculation -->
                            <div class="col-lg-12">
                                <div class="alert alert-success">
                                    <h6><i class="fa fa-calculator"></i> Preview Calculation:</h6>
                                    <p class="mb-0" id="preview-text">
                                        For a <strong>₹<span id="preview-amount">500</span></strong> order, 
                                        customer will earn <strong><span id="preview-points">5</span> points</strong> 
                                        (worth ₹<span id="preview-value">5</span>)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary w-auto my-4" type="submit">
                            <i class="fa fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('appendJs')
<script>
    $(document).ready(function() {
        @if(session('success'))
            toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
            toastr.success("{{ session('success') }}");
        @endif
        
        @if(session('error'))
            toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
            toastr.error("{{ session('error') }}");
        @endif

        // Calculate preview
        function updatePreview() {
            var minOrder = parseFloat($('#min_order_value').val()) || 100;
            var baseAmount = parseFloat($('#reward_base_amount').val()) || 100;
            var points = parseInt($('#reward_points').val()) || 1;
            var pointValue = parseFloat($('#points_value').val()) || 1;
            
            // Example order of 500
            var exampleOrder = 500;
            
            if (exampleOrder >= minOrder) {
                var earnedPoints = Math.floor((exampleOrder / baseAmount) * points);
                var rupeeValue = earnedPoints * pointValue;
                
                $('#preview-amount').text(exampleOrder);
                $('#preview-points').text(earnedPoints);
                $('#preview-value').text(rupeeValue.toFixed(2));
            } else {
                $('#preview-points').text('0');
                $('#preview-value').text('0');
            }
        }

        // Update preview on input change
        $('#min_order_value, #reward_base_amount, #reward_points, #points_value').on('input', updatePreview);
        
        // Initial preview
        updatePreview();
    });
</script>
@endpush
