<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WalletSeeder::class,
            AdminSeeder::class,
            PointsConfigSeeder::class,
            RewardSettingSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ProductColorSeeder::class,
            ProductVariantSeeder::class,
            BannerSeeder::class,
        ]);
    }
}
