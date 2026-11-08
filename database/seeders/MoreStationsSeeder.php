<?php

namespace Database\Seeders;

use App\Models\FuelType;
use App\Models\GasolineStation;
use App\Models\News;
use App\Models\StationPersonnel;
use App\Models\StationService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Adds extra DEMO stations across Manolo Fortich barangays without touching
 * existing data (safe to run on a live database; skips any station whose
 * name already exists). Coordinates are approximate placeholders so the map
 * and heatmap have spread-out data. For real stations, register them from the
 * LGU "Register New Station" page and pin the exact spot on the map.
 *
 *   php artisan db:seed --class=MoreStationsSeeder
 */
class MoreStationsSeeder extends Seeder
{
    public function run(): void
    {
        $lgu = User::where('role', 'lgu_admin')->first();
        $fuel = FuelType::whereIn('slug', ['regular', 'premium', 'diesel'])->get()->keyBy('slug');

        if (! $lgu || $fuel->count() < 3) {
            $this->command?->warn('Run the main DatabaseSeeder first (LGU admin and fuel types are missing).');

            return;
        }

        $stations = [
            ['Damilag Highway Fuel', 'Sayre Highway, Damilag', 'Damilag', 8.3520, 124.7870, [64.80, 68.20, 57.90], ['enough', 'enough', 'enough']],
            ['Dalirig Roadside Gas', 'Purok 2, Dalirig', 'Dalirig', 8.3950, 124.8310, [65.60, 69.10, 58.70], ['enough', 'almost_empty', 'enough']],
            ['Maluko Fuel Stop', 'Maluko Crossing, Maluko', 'Maluko', 8.3050, 124.8900, [66.40, 69.80, 59.60], ['almost_empty', 'enough', 'no_fuel']],
            ['Sankanan Petro Hub', 'Poblacion, Sankanan', 'Sankanan', 8.4000, 124.9000, [67.10, 70.40, 60.30], ['enough', 'enough', 'enough']],
            ['Dahilayan Mountain Gas', 'Dahilayan Road, Dahilayan', 'Dahilayan', 8.2020, 124.8890, [68.00, 71.20, 61.00], ['enough', 'no_fuel', 'enough']],
            ['Lunocan Fuel Center', 'Purok 4, Lunocan', 'Lunocan', 8.3560, 124.8960, [65.20, 68.60, 58.20], ['enough', 'enough', 'almost_empty']],
            ['Santiago Gas Station', 'Santiago Junction, Santiago', 'Santiago', 8.3420, 124.8380, [64.40, 67.80, 57.40], ['enough', 'enough', 'enough']],
            ['Lingion Fuel Depot', 'Purok 1, Lingion', 'Lingion', 8.4050, 124.8700, [65.90, 69.30, 58.90], ['almost_empty', 'enough', 'enough']],
            ['Alae Roadside Fuel', 'Alae-Tankulan Road, Alae', 'Alae', 8.3590, 124.8730, [65.50, 68.90, 58.50], ['enough', 'enough', 'enough']],
            ['Tankulan Express Fuel', 'Sayre Highway, Tankulan', 'Tankulan', 8.3740, 124.8660, [64.90, 68.30, 57.80], ['enough', 'almost_empty', 'enough']],
            ['Diclum Fuel Station', 'Diclum Crossing, Diclum', 'Diclum', 8.3300, 124.8150, [65.10, 68.50, 58.30], ['enough', 'enough', 'enough']],
            ['Guilang-guilang Gas', 'Purok 5, Guilang-guilang', 'Guilang-guilang', 8.3850, 124.8050, [64.70, 68.10, 57.70], ['enough', 'almost_empty', 'enough']],
            ['Kalugmanan Fuel Point', 'Kalugmanan Road, Kalugmanan', 'Kalugmanan', 8.3980, 124.8550, [66.00, 69.40, 59.10], ['almost_empty', 'enough', 'enough']],
            ['Mantibugao Petro Stop', 'Mantibugao Highway, Mantibugao', 'Mantibugao', 8.3200, 124.8600, [66.80, 70.10, 59.90], ['enough', 'enough', 'no_fuel']],
            ['Minsuro Roadside Gas', 'Purok 2, Minsuro', 'Minsuro', 8.3700, 124.9100, [67.30, 70.60, 60.50], ['enough', 'no_fuel', 'enough']],
            ['San Miguel Fuel Depot', 'San Miguel Junction, San Miguel', 'San Miguel', 8.2800, 124.8700, [67.60, 70.90, 60.80], ['enough', 'enough', 'almost_empty']],
            ['Santo Nino Gas Station', 'Poblacion, Santo Nino', 'Santo Niño', 8.2500, 124.8800, [68.20, 71.40, 61.20], ['almost_empty', 'enough', 'enough']],
            ['Ticala Fuel Center', 'Ticala Road, Ticala', 'Ticala', 8.3400, 124.9250, [66.50, 69.90, 59.40], ['enough', 'enough', 'enough']],
            ['Damilag Plaza Fuel', 'Damilag Plaza, Damilag', 'Damilag', 8.3560, 124.7920, [64.60, 68.00, 57.60], ['enough', 'enough', 'enough']],
            ['Dalirig Junction Gas', 'Dalirig Junction, Dalirig', 'Dalirig', 8.3900, 124.8380, [65.80, 69.20, 58.80], ['enough', 'almost_empty', 'enough']],
            ['Lindaban Highway Fuel', 'Sayre Highway, Lindaban', 'Lindaban', 8.3770, 124.8480, [64.20, 67.60, 57.20], ['enough', 'enough', 'enough']],
            ['Tankulan Central Gas', 'Corner Rizal St, Tankulan', 'Tankulan', 8.3680, 124.8620, [65.30, 68.70, 58.40], ['almost_empty', 'enough', 'enough']],
        ];

        $serviceKeys = array_keys(StationService::CATALOG);
        $n = User::where('email', 'like', 'manager%@geogasmanfort.test')->count() + 10;

        foreach ($stations as $i => [$name, $address, $barangay, $lat, $lng, $prices, $avail]) {
            if (GasolineStation::where('station_name', $name)->exists()) {
                continue;
            }
            $n++;

            $station = GasolineStation::create([
                'station_name' => $name,
                'company_owner' => $name.' Corp.',
                'email' => "contact{$n}@geogasmanfort.test",
                'address' => $address,
                'barangay' => $barangay,
                'municipality' => 'Manolo Fortich',
                'province' => 'Bukidnon',
                'latitude' => $lat,
                'longitude' => $lng,
                'contact_number' => sprintf('0917-000-%04d', $n),
                'status' => 'active',
            ]);

            foreach (StationService::CATALOG as $key => $label) {
                $station->services()->create([
                    'service_key' => $key,
                    'label' => $label,
                    'available' => in_array($key, array_slice($serviceKeys, $i % 3, 3 + ($i % 4))),
                ]);
            }

            $manager = User::create([
                'name' => 'Manager - '.$name,
                'email' => "manager{$n}@geogasmanfort.test",
                'password' => Hash::make('password'),
                'role' => 'manager',
                'status' => 'active',
            ]);
            StationPersonnel::create(['user_id' => $manager->id, 'station_id' => $station->id, 'added_by' => $lgu->id]);

            foreach (['regular', 'premium', 'diesel'] as $k => $slug) {
                $station->fuelPrices()->create([
                    'fuel_type_id' => $fuel[$slug]->id,
                    'price' => $prices[$k],
                    'availability_status' => $avail[$k],
                    'updated_by' => $manager->id,
                    'created_at' => now()->subHours(3 + $i * 5),
                    'updated_at' => now()->subHours(3 + $i * 5),
                ]);
            }
        }

        // A couple of published announcements so the public News section is not empty.
        foreach ([
            ['Welcome to GeoGas ManFort', 'lgu_announcement', 'GeoGas ManFort helps residents of Manolo Fortich find nearby gasoline stations, compare current fuel prices and check fuel availability. Prices are updated directly by the stations. Report any wrong information using Report an Issue.'],
            ['Check the price heatmap', 'important_notice', 'Use the Heatmap switch on the home map to see where fuel prices are relatively higher or lower across the municipality. Colors compare stations with each other and are not official price classifications.'],
        ] as [$title, $category, $content]) {
            News::firstOrCreate(['title' => $title], [
                'category' => $category,
                'content' => $content,
                'status' => 'published',
                'published_at' => now()->subHours(2),
                'created_by' => $lgu->id,
            ]);
        }

        // One explainer so the "Philippines" tab is not empty. It states no current prices;
        // the LGU should replace/extend it with real national news (source link required).
        News::firstOrCreate(['title' => 'How weekly pump price adjustments work in the Philippines'], [
            'category' => 'other',
            'scope' => 'national',
            'source_name' => 'GeoGas ManFort (explainer)',
            'source_url' => 'https://www.doe.gov.ph',
            'content' => 'In the Philippines, oil companies adjust pump prices based on world oil prices and the peso-dollar exchange rate, and changes are commonly announced a few days before they take effect, usually at the start of the week. Stations may price differently from one another, so check the prices posted here for stations near you. The LGU will post national fuel news on this page.',
            'status' => 'published',
            'published_at' => now()->subHour(),
            'created_by' => $lgu->id,
        ]);
    }
}
