<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\FuelType;
use App\Models\GasolineStation;
use App\Models\StationPersonnel;
use App\Models\StationService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DEVELOPMENT / TEST DATA ONLY.
 * Fuel prices, availability, and complaints seeded here are illustrative
 * sample data — they are NOT real current fuel prices or real reports.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Fuel types ---
        $fuelTypes = collect([
            ['name' => 'Regular/Unleaded', 'slug' => 'regular', 'sort_order' => 1],
            ['name' => 'Premium', 'slug' => 'premium', 'sort_order' => 2],
            ['name' => 'Diesel', 'slug' => 'diesel', 'sort_order' => 3],
        ])->map(fn ($data) => FuelType::create($data));

        // --- LGU Admin account ---
        $lguAdmin = User::create([
            'name' => 'LGU Administrator',
            'email' => 'lgu@geogasmanfort.test',
            'password' => Hash::make('password'),
            'role' => 'lgu_admin',
            'status' => 'active',
        ]);

        // --- Sample stations in Manolo Fortich, Bukidnon ---
        $stationsData = [
            [
                'station_name' => 'Tankulan Fuel Station',
                'address' => 'National Highway, Poblacion',
                'barangay' => 'Tankulan',
                'latitude' => 8.3696, 'longitude' => 124.8642,
                'contact_number' => '0917-000-0001',
                'prices' => ['regular' => 65.00, 'premium' => 68.50, 'diesel' => 58.00],
                'availability' => ['regular' => 'enough', 'premium' => 'enough', 'diesel' => 'almost_empty'],
            ],
            [
                'station_name' => 'Manolo Fortich Petro Center',
                'address' => 'Sayre Highway, Tankulan',
                'barangay' => 'Tankulan',
                'latitude' => 8.3721, 'longitude' => 124.8598,
                'contact_number' => '0917-000-0002',
                'prices' => ['regular' => 64.50, 'premium' => 67.90, 'diesel' => 57.50],
                'availability' => ['regular' => 'enough', 'premium' => 'no_fuel', 'diesel' => 'enough'],
            ],
            [
                'station_name' => 'Bukidnon Gas Point',
                'address' => 'Purok 3, Alae',
                'barangay' => 'Alae',
                'latitude' => 8.3658, 'longitude' => 124.8671,
                'contact_number' => '0917-000-0003',
                'prices' => ['regular' => 66.00, 'premium' => 69.00, 'diesel' => 59.00],
                'availability' => ['regular' => 'almost_empty', 'premium' => 'enough', 'diesel' => 'enough'],
            ],
            [
                'station_name' => 'Lindaban Fuel Depot',
                'address' => 'Purok 1, Lindaban',
                'barangay' => 'Lindaban',
                'latitude' => 8.3801, 'longitude' => 124.8532,
                'contact_number' => '0917-000-0004',
                'prices' => ['regular' => 63.90, 'premium' => 67.20, 'diesel' => 56.80],
                'availability' => ['regular' => 'enough', 'premium' => 'enough', 'diesel' => 'enough'],
            ],
        ];

        $serviceKeys = array_keys(StationService::CATALOG);

        foreach ($stationsData as $index => $data) {
            $station = GasolineStation::create([
                'station_name' => $data['station_name'],
                'company_owner' => $data['station_name'].' Corp.',
                'email' => 'contact'.$index.'@geogasmanfort.test',
                'address' => $data['address'],
                'barangay' => $data['barangay'],
                'municipality' => 'Manolo Fortich',
                'province' => 'Bukidnon',
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'contact_number' => $data['contact_number'],
                'status' => 'active',
            ]);

            // Services: give each station a plausible mix
            foreach (StationService::CATALOG as $key => $label) {
                $station->services()->create([
                    'service_key' => $key,
                    'label' => $label,
                    'available' => in_array($key, array_slice($serviceKeys, 0, 4 + $index)),
                ]);
            }

            $manager = User::create([
                'name' => 'Manager - '.$data['station_name'],
                'email' => "manager{$index}@geogasmanfort.test",
                'password' => Hash::make('password'),
                'role' => 'manager',
                'status' => 'active',
            ]);

            StationPersonnel::create([
                'user_id' => $manager->id,
                'station_id' => $station->id,
                'added_by' => $lguAdmin->id,
            ]);

            // One staff member per station, limited permissions
            $staff = User::create([
                'name' => 'Staff - '.$data['station_name'],
                'email' => "staff{$index}@geogasmanfort.test",
                'password' => Hash::make('password'),
                'role' => 'staff',
                'status' => 'active',
            ]);

            StationPersonnel::create([
                'user_id' => $staff->id,
                'station_id' => $station->id,
                'added_by' => $manager->id,
                'permissions' => ['view_dashboard', 'update_prices', 'update_availability', 'view_price_history'],
            ]);

            // Fuel prices + availability (direct update, no approval)
            foreach ($fuelTypes as $fuelType) {
                $station->fuelPrices()->create([
                    'fuel_type_id' => $fuelType->id,
                    'price' => $data['prices'][$fuelType->slug],
                    'availability_status' => $data['availability'][$fuelType->slug],
                    'updated_by' => $manager->id,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]);
            }

            if ($index === 0) {
                // Show a more recent price update for the price-change indicator demo
                $station->fuelPrices()->create([
                    'fuel_type_id' => $fuelTypes->firstWhere('slug', 'regular')->id,
                    'price' => $data['prices']['regular'] + 0.50,
                    'availability_status' => 'enough',
                    'updated_by' => $manager->id,
                ]);
            }
        }

        // --- Sample complaint ---
        $firstStation = GasolineStation::first();
        Complaint::create([
            'reference_no' => Complaint::generateReferenceNo(),
            'name' => 'Juan Dela Cruz',
            'contact' => '0917-555-0000',
            'category' => 'price_not_updated',
            'station_id' => $firstStation->id,
            'subject' => 'Diesel price on the map looks outdated',
            'description' => 'The diesel price shown for this station has not changed in over a week, but the pump price is different.',
            'status' => 'pending',
            'submitter_ip' => '127.0.0.1',
        ]);
    }
}
