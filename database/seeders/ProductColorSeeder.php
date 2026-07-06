<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            [
                'id' => 1,
                'product_id' => 1,
                'color' => 'silver',
                'images' => json_encode(['product-colors/0G9ZFJeliNWOmzvNqZ3PfqA3FABpjrCflppOSCBU.avif']),
            ],
            [
                'id' => 2,
                'product_id' => 2,
                'color' => 'ruby',
                'images' => json_encode(['product-colors/1CIj0LsnOEMmf00xwFJCZJ5bx3weiXnN2ujWPOqT.avif']),
            ],
            [
                'id' => 3,
                'product_id' => 2,
                'color' => 'green',
                'images' => json_encode(['product-colors/2vs7QJSjNfWhOrh1U5UdRFOvCqtpvd3yo44FtJ7n.avif']),
            ],
            [
                'id' => 4,
                'product_id' => 3,
                'color' => 'green',
                'images' => json_encode(['product-colors/39fAcLUlmtE4BmjQNmrtkXKzU5mNtqq8aLfNB7B2.webp']),
            ],
            [
                'id' => 5,
                'product_id' => 4,
                'color' => 'gold',
                'images' => json_encode(['product-colors/3TmVDiNn3Bs4CI2s7tZstOfW6tZlywehRpwXP2as.webp']),
            ]
        ];

        foreach ($colors as $col) {
            DB::table('product_colors')->insert(array_merge($col, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
