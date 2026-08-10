<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => 'percentage',
                'value' => 10,
                'min_cart_amount' => 100,
                'usage_limit' => 500,
                'used_count' => 0,
                'expires_at' => now()->addMonths(6),
                'status' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'FLAT100'],
            [
                'type' => 'fixed',
                'value' => 100,
                'min_cart_amount' => 500,
                'usage_limit' => 200,
                'used_count' => 0,
                'expires_at' => now()->addMonths(6),
                'status' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'SAAYAL20'],
            [
                'type' => 'percentage',
                'value' => 20,
                'min_cart_amount' => 1000,
                'usage_limit' => 100,
                'used_count' => 0,
                'expires_at' => now()->addMonths(6),
                'status' => true,
            ]
        );
    }
}
