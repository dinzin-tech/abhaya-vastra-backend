<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Coupon;

// Get the coupon
$coupon = Coupon::find(1);
$userId = 1;
$cartAmount = 1000;

if ($coupon) {
    echo "Testing Coupon: " . $coupon->code . "\n";
    
    // Basic info
    echo "Type: " . $coupon->type . "\n";
    echo "Value: " . $coupon->value . "\n";
    echo "Min Cart: " . $coupon->min_cart_amount . "\n";
    echo "Status: " . ($coupon->status ? 'Active' : 'Inactive') . "\n";
    
    // Check if user has used this coupon
    $userCoupon = $coupon->users()->where('user_id', $userId)->first();
    echo "\nUser Coupon Status: " . ($userCoupon ? 'Exists' : 'Not Exists') . "\n";
    if ($userCoupon) {
        echo "Used: " . ($userCoupon->pivot->used ? 'Yes' : 'No') . "\n";
    }
    
    // Test validation
    $isValid = $coupon->isValid($cartAmount, $userId);
    echo "\nIs Valid: " . ($isValid ? 'Yes' : 'No') . "\n";
    
} else {
    echo "Coupon not found!\n";
}
