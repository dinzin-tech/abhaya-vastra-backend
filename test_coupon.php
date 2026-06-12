<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(IllwareConsoleKernel::class);
$kernel->bootstrap();

use App\Models\Coupon;

// Test coupon validation
$coupon = Coupon::find(1);
$userId = 1;
$cartAmount = 1000;

if ($coupon) {
    echo "Testing Coupon: " . $coupon->code . "\n";
    echo "Type: " . $coupon->type . "\n";
    echo "Value: " . $coupon->value . "\n";
    echo "Min Cart Amount: " . $coupon->min_cart_amount . "\n";
    echo "Usage Limit: " . $coupon->usage_limit . "\n";
    echo "Used Count: " . $coupon->used_count . "\n";
    echo "Status: " . ($coupon->status ? 'Active' : 'Inactive') . "\n";
    
    // Check if user has used this coupon
    $userCoupon = $coupon->users()->where('user_id', $userId)->first();
    echo "\nUser Coupon Status: " . ($userCoupon ? 'Exists' : 'Not Exists') . "\n";
    if ($userCoupon) {
        echo "Used Status: " . ($userCoupon->pivot->used ? 'Used' : 'Not Used') . "\n";
    }
    
    // Test validation
    $isValid = $coupon->isValid($cartAmount, $userId);
    echo "\nIs Valid: " . ($isValid ? 'Yes' : 'No') . "\n";
    
    // Check basic validation
    $basicCheck = $coupon->status &&
        ($coupon->expires_at == null || \Carbon\Carbon::parse($coupon->expires_at)->isFuture()) &&
        ($coupon->usage_limit === null || $coupon->used_count < $coupon->usage_limit) &&
        $cartAmount >= $coupon->min_cart_amount;
        
    echo "Basic Validation: " . ($basicCheck ? 'Passed' : 'Failed') . "\n";
    
    if (!$basicCheck) {
        if (!$coupon->status) echo "- Coupon is inactive\n";
        if ($coupon->expires_at && \Carbon\Carbon::parse($coupon->expires_at)->isPast()) 
            echo "- Coupon has expired\n";
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) 
            echo "- Usage limit reached\n";
        if ($cartAmount < $coupon->min_cart_amount) 
            echo "- Cart amount too low. Min: " . $coupon->min_cart_amount . "\n";
    }
    
    // Check if user has already used this coupon
    if ($basicCheck && $userId) {
        $userCoupon = $coupon->users()->where('user_id', $userId)->first();
        if ($userCoupon && $userCoupon->pivot->used) {
            echo "- User has already used this coupon\n";
        }
    }
} else {
    echo "Coupon not found!\n";
}
