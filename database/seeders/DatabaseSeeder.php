<?php

namespace Database\Seeders;

use App\Models\GasolineStation;
use App\Models\StationPersonnel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DEVELOPMENT / TEST DATA ONLY.
 * Fuel prices seeded here are illustrative sample data for demoing the
 * verification workflow — they are NOT real current fuel prices.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Administrator account ---
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@geogasmanfort.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // --- Sample stations in Barangay Tankulan, Manolo Fortich, Bukidnon ---
        $stations = [
            [
                'station_name' => 'Tankulan Fuel Station',
                'address' => 'National Highway, Poblacion',
                'latitude' => 8.3696,
                'longitude' => 124.8642,
                'contact_number' => '0917-000-0001',
            ],
            [
                'station_name' => 'Manolo Fortich Petro Center',
                'address' => 'Sayre Highway, Tankulan',
                'latitude' => 8.3721,
                'longitude' => 124.8598,
                'contact_number' => '0917-000-0002',
            ],
            [
                'station_name' => 'Bukidnon Gas Point',
                'address' => 'Purok 3, Tankulan',
                'latitude' => 8.3658,
                'longitude' => 124.8671,
                'contact_number' => '0917-000-0003',
            ],
        ];

        foreach ($stations as $index => $data) {
            $station = GasolineStation::create([
                ...$data,
                'barangay' => 'Tankulan',
                'municipality' => 'Manolo Fortich',
                'province' => 'Bukidnon',
                'status' => 'active',
            ]);

            $personnelUser = User::create([
                'name' => 'Station Staff '.($index + 1),
                'email' => "staff{$index}@geogasmanfort.test",
                'password' => Hash::make('password'),
                'role' => 'station_personnel',
                'status' => 'active',
            ]);

            StationPersonnel::create([
                'user_id' => $personnelUser->id,
                'station_id' => $station->id,
            ]);

            // One approved (sample) price + one pending price awaiting verification
            $station->fuelPrices()->create([
                'gasoline_price' => 65 + $index,
                'diesel_price' => 58 + $index,
                'effective_date' => now()->subDays(2),
                'status' => 'approved',
                'submitted_by' => $personnelUser->id,
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(2),
            ]);

            $station->fuelPrices()->create([
                'gasoline_price' => 66 + $index,
                'diesel_price' => 59 + $index,
                'effective_date' => now(),
                'status' => 'pending',
                'submitted_by' => $personnelUser->id,
            ]);
        }
    }
}
