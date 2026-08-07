<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lookbook;

class LookbookSeeder extends Seeder
{
    public function run()
    {
        Lookbook::truncate();

        $items = [
            [
                'title' => 'ABHAYA VASTRA COUTURE',
                'subtitle' => 'EDITORIAL RUNWAY COLLECTION',
                'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1200&auto=format&fit=crop',
                'link' => '/all-products',
                'size' => 'large',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => "WOMEN'S READY TO WEAR",
                'subtitle' => 'TIMELESS ELEGANCE',
                'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=800&auto=format&fit=crop',
                'link' => '/all-products',
                'size' => 'medium',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'BESPOKE TAILORING',
                'subtitle' => 'CUSTOM MADE LUXURY',
                'image' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?q=80&w=800&auto=format&fit=crop',
                'link' => '/customization',
                'size' => 'medium',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'FINE ACCESSORIES',
                'subtitle' => 'GALLERIA LEATHERWEAR',
                'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?q=80&w=1200&auto=format&fit=crop',
                'link' => '/featured-products',
                'size' => 'full',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Lookbook::create($item);
        }
    }
}
