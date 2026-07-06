<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            ['image' => 'banners/BsuBLqxw6EPbw5hL89iLE8cPlwqwUlb2Hq2aYKPT.webp'],
            ['image' => 'banners/haJap98lOHffk0DNtUmBMABtxsMgH0SGUhnYTlmQ.webp'],
            ['image' => 'banners/nkxf7EahsccMuDlU913BtcQTHEapOWIO6YtbCEg4.webp'],
            ['image' => 'banners/Yuf6MiG92CwPapFtw6ojKCpLtULK5HvFU9k1nElJ.webp']
        ];

        foreach ($banners as $b) {
            DB::table('banners')->insert(array_merge($b, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
