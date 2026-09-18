<?php

namespace App\Support;

use App\Models\User;

/**
 * Single source of truth for the authenticated-area sidebars, so adding or
 * reordering a nav item is a one-file change instead of editing every view.
 */
class Nav
{
    public static function stationSidebar(User $user): array
    {
        $items = collect([
            ['route' => 'station.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'perm' => 'view_dashboard'],
            ['route' => 'station.prices.edit', 'label' => 'Update Fuel Prices', 'icon' => 'bi-pencil-square', 'perm' => 'update_prices'],
            ['route' => 'station.history', 'label' => 'Price History', 'icon' => 'bi-clock-history', 'perm' => 'view_price_history'],
            ['route' => 'station.analytics.index', 'label' => 'Analytics', 'icon' => 'bi-graph-up', 'perm' => 'view_analytics'],
            ['route' => 'station.services.edit', 'label' => 'Station Services', 'icon' => 'bi-tools', 'perm' => 'manage_services'],
            ['route' => 'station.info.edit', 'label' => 'Station Info', 'icon' => 'bi-building', 'perm' => 'edit_station_info'],
            ['route' => 'station.reports.index', 'label' => 'Reports', 'icon' => 'bi-file-earmark-text-fill', 'perm' => 'view_reports'],
        ])->filter(fn ($i) => $user->hasStationPermission($i['perm']))->values()->all();

        if ($user->isManager()) {
            $items[] = ['route' => 'station.staff.index', 'label' => 'Staff Accounts', 'icon' => 'bi-people-fill'];
        }

        $items[] = ['route' => 'station.session-logs.index', 'label' => 'Session Logs', 'icon' => 'bi-clock-history'];
        $items[] = ['route' => 'account.settings.edit', 'label' => 'Account Settings', 'icon' => 'bi-gear-fill'];

        return $items;
    }

    public static function lguSidebar(): array
    {
        return [
            ['route' => 'lgu.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
            ['route' => 'lgu.stations.index', 'label' => 'Stations', 'icon' => 'bi-shop'],
            ['route' => 'lgu.users.index', 'label' => 'Users', 'icon' => 'bi-people-fill'],
            ['route' => 'lgu.complaints.index', 'label' => 'Complaints', 'icon' => 'bi-flag-fill'],
            ['route' => 'lgu.analytics.index', 'label' => 'Analytics', 'icon' => 'bi-graph-up'],
            ['route' => 'lgu.reports.index', 'label' => 'Reports', 'icon' => 'bi-file-earmark-text-fill'],
            ['route' => 'lgu.session-logs.index', 'label' => 'Session Logs', 'icon' => 'bi-clock-history'],
            ['route' => 'account.settings.edit', 'label' => 'Account Settings', 'icon' => 'bi-gear-fill'],
        ];
    }
}
