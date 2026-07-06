<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'id' => 1,
                'category_id' => 1,
                'name' => "Men's Classic Silver Ring",
                'slug' => 'mens-classic-silver-ring',
                'description' => 'A timeless classic sterling silver ring for men, suitable for any occasion.',
                'best_seller' => 1,
                'main_image' => 'products/2HRzPMB5QbeSRHZV7oEoMVgO4H2ZusXzcC0NZ3dM.avif',
                'zoomed_image' => 'products/2HRzPMB5QbeSRHZV7oEoMVgO4H2ZusXzcC0NZ3dM.avif',
                'customizable' => 0,
                'gender' => 'male',
            ],
            [
                'id' => 2,
                'category_id' => 2,
                'name' => 'ZM101604 - 925 Silver Bracelet',
                'slug' => 'zm101604-925-silver-bracelet',
                'description' => 'Beautiful 925 sterling silver double-layered bracelet studded with AD stones.',
                'best_seller' => 1,
                'main_image' => 'products/bBSNPNGrOVZTLKprLGGogQmxmRtWoJIwsnfT5HXD.avif',
                'zoomed_image' => 'products/bBSNPNGrOVZTLKprLGGogQmxmRtWoJIwsnfT5HXD.avif',
                'customizable' => 0,
                'gender' => 'female',
            ],
            [
                'id' => 3,
                'category_id' => 2,
                'name' => 'Premium Antique AD Stone Double Layer Long Necklace',
                'slug' => 'premium-antique-ad-stone-double-layer-long-necklace',
                'description' => 'Antique style double-layer long necklace set with matching earrings, studded with premium AD stones.',
                'best_seller' => 0,
                'main_image' => 'products/3taQ530ejE1xU2dPMfILgTwGOhS50cm9S9SH0vzH.avif',
                'zoomed_image' => 'products/3taQ530ejE1xU2dPMfILgTwGOhS50cm9S9SH0vzH.avif',
                'customizable' => 0,
                'gender' => 'female',
            ],
            [
                'id' => 4,
                'category_id' => 3,
                'name' => 'Customized Name Pendant',
                'slug' => 'customized-name-pendant',
                'description' => 'A personalized sterling silver name pendant crafted specifically for you.',
                'best_seller' => 0,
                'main_image' => 'products/e5YGSm4rtHlplLbGgcHCOBhVt2ufi1ehuhUtoQQl.avif',
                'zoomed_image' => 'products/e5YGSm4rtHlplLbGgcHCOBhVt2ufi1ehuhUtoQQl.avif',
                'customizable' => 1,
                'gender' => 'unisex',
            ]
        ];

        foreach ($products as $prod) {
            DB::table('products')->insert(array_merge($prod, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
