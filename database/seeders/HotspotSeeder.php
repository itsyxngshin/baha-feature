<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotspot;

class HotspotSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Panganiban Drive',
                'latitude' => 13.6256,
                'longitude' => 123.1930,
                'elevation_m' => 4.5,
                'drainage_level' => 6,
                'rainfall_mm_hr' => 12.5, // Initial dummy data
            ],
            [
                'name' => 'Magsaysay Avenue',
                'latitude' => 13.6300,
                'longitude' => 123.1970,
                'elevation_m' => 5.2,
                'drainage_level' => 8,
                'rainfall_mm_hr' => 8.0,
            ],
            [
                'name' => 'Centro (Gen. Luna)',
                'latitude' => 13.6210,
                'longitude' => 123.1850,
                'elevation_m' => 3.8,
                'drainage_level' => 4,
                'rainfall_mm_hr' => 15.0,
            ],
             [
                'name' => 'Diversion Road',
                'latitude' => 13.6150,
                'longitude' => 123.2100,
                'elevation_m' => 6.0,
                'drainage_level' => 9,
                'rainfall_mm_hr' => 5.0,
            ]
        ];

        foreach ($zones as $zone) {
            Hotspot::create($zone);
        }
    }
} 