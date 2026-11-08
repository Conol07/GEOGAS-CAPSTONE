<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Adds starter news WITHOUT touching stations, prices or accounts.
 * Safe to run more than once (matched by title).
 * Run: php artisan db:seed --class=NationalNewsSeeder
 */
class NationalNewsSeeder extends Seeder
{
    public function run(): void
    {
        $lgu = User::where('role', 'lgu_admin')->orderBy('id')->first();
        if (! $lgu) {
            $this->command?->warn('No LGU admin account found; nothing seeded.');

            return;
        }

        $items = [
            ['Welcome to GeoGas ManFort', 'lgu_announcement', 'local', null, null,
                'GeoGas ManFort helps residents of Manolo Fortich find nearby gasoline stations, compare current fuel prices and check fuel availability. Prices are updated directly by the stations. Report any wrong information using Report an Issue.'],
            ['How weekly pump price adjustments work in the Philippines', 'other', 'national', 'GeoGas ManFort (explainer)', 'https://www.doe.gov.ph',
                'In the Philippines, oil companies adjust pump prices based on world oil prices and the peso-dollar exchange rate, and changes are commonly announced a few days before they take effect, usually at the start of the week. Stations may price differently from one another, so check the prices posted here for stations near you. The LGU will post national fuel news on this page.'],
            ['Where to find official fuel price information', 'important_notice', 'national', 'Department of Energy', 'https://www.doe.gov.ph',
                'The Department of Energy publishes oil industry information and advisories for the whole country. Prices on GeoGas ManFort are reported by the stations in Manolo Fortich and may differ from national averages. Sample item: the LGU should replace it with current national news.'],
        ];

        foreach ($items as [$title, $cat, $scope, $src, $url, $content]) {
            News::firstOrCreate(['title' => $title], [
                'category' => $cat, 'scope' => $scope, 'source_name' => $src, 'source_url' => $url,
                'content' => $content, 'status' => 'published',
                'published_at' => now()->subHour(), 'created_by' => $lgu->id,
            ]);
        }
    }
}
