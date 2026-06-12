<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PointsConfig;

class PointsConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'min_amount' => 100,
                'max_amount' => 200,
                'points' => 50,
                'coin_value' => 1,
                'status' => 1,
            ],
            [
                'min_amount' => 200,
                'max_amount' => 500,
                'points' => 120,
                'coin_value' => 1,
                'status' => 1,
            ],
            [
                'min_amount' => 500,
                'max_amount' => 1000,
                'points' => 300,
                'coin_value' => 1,
                'status' => 1,
            ],
            [
                'min_amount' => 1000,
                'max_amount' => null,
                'points' => 650,
                'coin_value' => 1,
                'status' => 1,
            ],
        ];

        foreach ($packages as $package) {
            PointsConfig::updateOrCreate(
                ['min_amount' => $package['min_amount']],
                $package
            );
        }
    }
}
