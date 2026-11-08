<?php

namespace App\Support;

use App\Models\User;

/**
 * Single source of truth for the authenticated-area sidebars. Each sidebar is
 * a list of collapsible groups: ['label','icon','items'=>[['route','label','icon']]].
 * Items the account may not use are pruned here AND blocked by route middleware.
 */
class Nav
{
    public static function stationSidebar(User $user): array
    {
        $can = fn (string $perm) => $user->hasStationPermission($perm);

        $groups = [
            ['label' => 'Overview', 'icon' => 'bi-grid-fill', 'items' => [
                ['route' => 'station.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'show' => $can('view_dashboard')],
                ['route' => 'station.analytics.index', 'label' => 'Analytics', 'icon' => 'bi-graph-up', 'show' => $can('view_analytics')],
            ]],
            ['label' => 'Station Operations', 'icon' => 'bi-fuel-pump-fill', 'items' => [
                ['route' => 'station.info.edit', 'label' => 'Station Profile', 'icon' => 'bi-building', 'show' => $can('edit_station_info')],
                ['route' => 'station.prices.edit', 'label' => 'Update Fuel Prices', 'icon' => 'bi-pencil-square', 'show' => $can('update_prices')],
                ['route' => 'station.fuel-types.create', 'label' => 'Add Fuel Type', 'icon' => 'bi-plus-circle', 'show' => $can('update_prices')],
                ['route' => 'station.history', 'label' => 'Price History', 'icon' => 'bi-clock-history', 'show' => $can('view_price_history')],
                ['route' => 'station.services.edit', 'label' => 'Station Services', 'icon' => 'bi-tools', 'show' => $can('manage_services')],
            ]],
            ['label' => 'Staff Management', 'icon' => 'bi-people-fill', 'items' => [
                ['route' => 'station.staff.index', 'label' => 'Staff Accounts', 'icon' => 'bi-person-badge', 'show' => $user->isManager()],
            ]],
            ['label' => 'Reports', 'icon' => 'bi-file-earmark-text-fill', 'items' => [
                ['route' => 'station.reports.index', 'label' => 'Reports', 'icon' => 'bi-file-earmark-text', 'show' => $can('view_reports')],
            ]],
            ['label' => 'Account', 'icon' => 'bi-person-circle', 'items' => [
                ['route' => 'station.session-logs.index', 'label' => 'Session Logs', 'icon' => 'bi-journal-text', 'show' => true],
                ['route' => 'account.settings.edit', 'label' => 'Account Settings', 'icon' => 'bi-gear-fill', 'show' => true],
            ]],
        ];

        return self::prune($groups);
    }

    public static function lguSidebar(): array
    {
        $groups = [
            ['label' => 'Overview', 'icon' => 'bi-grid-fill', 'items' => [
                ['route' => 'lgu.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
                ['route' => 'lgu.analytics.index', 'label' => 'Analytics', 'icon' => 'bi-graph-up'],
            ]],
            ['label' => 'Station Management', 'icon' => 'bi-shop', 'items' => [
                ['route' => 'lgu.stations.index', 'label' => 'Fuel Stations', 'icon' => 'bi-shop'],
            ]],
            ['label' => 'Fuel Monitoring', 'icon' => 'bi-geo-alt-fill', 'items' => [
                ['route' => 'lgu.gis.index', 'label' => 'GIS Map / Heatmap', 'icon' => 'bi-map-fill'],
            ]],
            ['label' => 'Reports', 'icon' => 'bi-file-earmark-text-fill', 'items' => [
                ['route' => 'lgu.reports.index', 'label' => 'Reports', 'icon' => 'bi-file-earmark-text'],
            ]],
            ['label' => 'Community', 'icon' => 'bi-chat-square-text-fill', 'items' => [
                ['route' => 'lgu.complaints.index', 'label' => 'Complaints', 'icon' => 'bi-flag-fill'],
                ['route' => 'lgu.news.index', 'label' => 'News & Announcements', 'icon' => 'bi-megaphone-fill'],
            ]],
            ['label' => 'System Management', 'icon' => 'bi-sliders', 'items' => [
                ['route' => 'lgu.users.index', 'label' => 'Users', 'icon' => 'bi-people-fill'],
                ['route' => 'lgu.session-logs.index', 'label' => 'Session Logs', 'icon' => 'bi-journal-text'],
                ['route' => 'account.settings.edit', 'label' => 'Account Settings', 'icon' => 'bi-gear-fill'],
            ]],
        ];

        return self::prune($groups);
    }

    /** Drop hidden items and any group left empty. */
    private static function prune(array $groups): array
    {
        $out = [];
        foreach ($groups as $g) {
            $g['items'] = array_values(array_filter($g['items'], fn ($i) => $i['show'] ?? true));
            if ($g['items']) {
                $out[] = $g;
            }
        }

        return $out;
    }
}
