<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RewardSetting;

class RewardSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RewardSetting::updateOrCreate(
            ['id' => 1],
            [
                'min_order_value' => 100,
                'reward_base_amount' => 100,
                'reward_points' => 1,
                'points_value' => 1,
                'status' => 1,
            ]
        );
    }
}
