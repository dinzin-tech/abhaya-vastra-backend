<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'name' => 'Men',
                'slug' => 'men',
                'gender' => 'male',
                'main_image' => 'categories/1htHxuItHS3LfNRFKzGexEiYe1NhX4CyV44YdAGF.jpg',
                'zoomed_image' => 'categories/1htHxuItHS3LfNRFKzGexEiYe1NhX4CyV44YdAGF.jpg',
            ],
            [
                'id' => 2,
                'name' => 'Women',
                'slug' => 'women',
                'gender' => 'female',
                'main_image' => 'categories/0icIJqIRNbwIdxeeSwB9DeaUBWXLZZPZ5x8Rg6FU.webp',
                'zoomed_image' => 'categories/0icIJqIRNbwIdxeeSwB9DeaUBWXLZZPZ5x8Rg6FU.webp',
            ],
            [
                'id' => 3,
                'name' => 'Customization',
                'slug' => 'customization',
                'gender' => 'unisex',
                'main_image' => 'categories/2bx3N6wFjwXpfpEQ9l2QOzcltjieXctgqQETuCe8.webp',
                'zoomed_image' => 'categories/2bx3N6wFjwXpfpEQ9l2QOzcltjieXctgqQETuCe8.webp',
            ]
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert(array_merge($cat, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
