<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'USA' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'San Francisco'],
            'GBR' => ['London', 'Manchester', 'Birmingham', 'Liverpool', 'Edinburgh', 'Bristol'],
            'CAN' => ['Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Ottawa', 'Quebec City'],
            'AUS' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Canberra'],
            'DEU' => ['Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne', 'Stuttgart'],
            'FRA' => ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Bordeaux'],
            'IND' => ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Kolkata'],
            'EGY' => ['Cairo', 'Alexandria', 'Giza', 'Luxor', 'Aswan', 'Port Said'],
            'SAU' => ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar'],
            'ARE' => ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah', 'Fujairah'],
            'TUR' => ['Istanbul', 'Ankara', 'Izmir', 'Antalya', 'Bursa', 'Adana'],
            'NLD' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven', 'Groningen'],
        ];

        foreach ($cities as $countryCode => $cityNames) {
            $country = Country::where('code', $countryCode)->first();
            
            if (!$country) {
                continue;
            }

            foreach ($cityNames as $cityName) {
                City::firstOrCreate(
                    [
                        'country_id' => $country->id,
                        'name' => $cityName,
                    ],
                    [
                        'country_id' => $country->id,
                        'name' => $cityName,
                    ]
                );
            }
        }
    }
}
