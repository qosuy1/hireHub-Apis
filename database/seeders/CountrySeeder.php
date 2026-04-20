<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'United States', 'code' => 'USA', 'phone_code' => '+1'],
            ['name' => 'United Kingdom', 'code' => 'GBR', 'phone_code' => '+44'],
            ['name' => 'Canada', 'code' => 'CAN', 'phone_code' => '+1'],
            ['name' => 'Australia', 'code' => 'AUS', 'phone_code' => '+61'],
            ['name' => 'Germany', 'code' => 'DEU', 'phone_code' => '+49'],
            ['name' => 'France', 'code' => 'FRA', 'phone_code' => '+33'],
            ['name' => 'India', 'code' => 'IND', 'phone_code' => '+91'],
            ['name' => 'China', 'code' => 'CHN', 'phone_code' => '+86'],
            ['name' => 'Japan', 'code' => 'JPN', 'phone_code' => '+81'],
            ['name' => 'Brazil', 'code' => 'BRA', 'phone_code' => '+55'],
            ['name' => 'Egypt', 'code' => 'EGY', 'phone_code' => '+20'],
            ['name' => 'Saudi Arabia', 'code' => 'SAU', 'phone_code' => '+966'],
            ['name' => 'United Arab Emirates', 'code' => 'ARE', 'phone_code' => '+971'],
            ['name' => 'Turkey', 'code' => 'TUR', 'phone_code' => '+90'],
            ['name' => 'Netherlands', 'code' => 'NLD', 'phone_code' => '+31'],
            ['name' => 'Spain', 'code' => 'ESP', 'phone_code' => '+34'],
            ['name' => 'Italy', 'code' => 'ITA', 'phone_code' => '+39'],
            ['name' => 'Mexico', 'code' => 'MEX', 'phone_code' => '+52'],
            ['name' => 'South Korea', 'code' => 'KOR', 'phone_code' => '+82'],
            ['name' => 'Singapore', 'code' => 'SGP', 'phone_code' => '+65'],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['code' => $country['code']],
                $country
            );
        }
    }
}
