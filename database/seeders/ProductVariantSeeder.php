<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $variants = [
            [
                'product_id' => 1,
                'color_id' => 1,
                'size' => 'S',
                'stock' => 10,
                'price' => 999.00,
                'discount' => 10.00,
                'total_price' => 899.10,
                'weight' => 0.05,
            ],
            [
                'product_id' => 1,
                'color_id' => 1,
                'size' => 'M',
                'stock' => 15,
                'price' => 999.00,
                'discount' => 10.00,
                'total_price' => 899.10,
                'weight' => 0.05,
            ],
            [
                'product_id' => 2,
                'color_id' => 2,
                'size' => 'M',
                'stock' => 5,
                'price' => 1599.00,
                'discount' => 0.00,
                'total_price' => 1599.00,
                'weight' => 0.10,
            ],
            [
                'product_id' => 2,
                'color_id' => 3,
                'size' => 'M',
                'stock' => 8,
                'price' => 1599.00,
                'discount' => 0.00,
                'total_price' => 1599.00,
                'weight' => 0.10,
            ],
            [
                'product_id' => 3,
                'color_id' => 4,
                'size' => 'L',
                'stock' => 3,
                'price' => 2499.00,
                'discount' => 20.00,
                'total_price' => 1999.20,
                'weight' => 0.25,
            ],
            [
                'product_id' => 4,
                'color_id' => 5,
                'size' => 'One Size',
                'stock' => 50,
                'price' => 1299.00,
                'discount' => 0.00,
                'total_price' => 1299.00,
                'weight' => 0.08,
            ]
        ];

        foreach ($variants as $v) {
            DB::table('product_variants')->insert(array_merge($v, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
