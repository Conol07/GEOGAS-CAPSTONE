# GeoGas ManFort

**A Web-Based Fuel Price Monitoring and Geospatial Analysis System for Manolo Fortich, Bukidnon**

Public fuel-price and availability monitoring, per-barangay price comparison, a cheapest-fuel finder, a nearest-station finder, and a public complaint channel — backed by an LGU monitoring/analytics/reports console and a per-station Manager/Staff console with a direct (no-approval) price-update workflow.

> ⚠️ **Note on this scaffold:** this repository contains full application source (migrations, models, controllers, middleware, routes, Blade views, seeders) hand-written to the Laravel 11 file layout, built in a sandboxed environment without outbound access to Packagist — `vendor/`, `artisan`, and other skeleton files are **not included**. Follow the setup steps below on a machine with normal Composer access.

---

## Role structure

| Role | Login required | Can |
|---|---|---|
| **Public User** | No | View stations, prices, availability, services, map; compare prices; view price-by-area; find cheapest fuel; find nearest station; send a complaint |
| **LGU Admin** | Yes | Register stations (+ their manager account) with a map picker; monitor everything; view analytics; manage complaints; generate reports. **Cannot approve/reject prices** — that workflow has been removed entirely |
| **Gasoline Station Manager** | Yes | Full control of their own station: update prices/availability directly (no approval needed), manage services, create/manage staff with granular permissions, view reports — scoped to their station only |
| **Station Staff** | Yes | Whatever their manager grants: view dashboard, update prices, update availability, view price history, manage services, view/generate reports — never staff management, never another station |

## What changed from the previous version

- **Price approval workflow removed completely** — no approve/reject buttons, no pending-price status, no verification page/controller/routes. Manager/staff price updates are visible publicly the moment they're saved.
- **Fuel prices are now normalized** per fuel type (`fuel_types` + `fuel_prices`) instead of fixed gasoline/diesel/premium/regular columns, and **append-only** — every update inserts a new row, which doubles as price history and powers the price-change (increased/decreased/unchanged) indicator.
- **Availability status** (`no_fuel` / `almost_empty` / `enough`) is tracked per fuel type alongside price, shown everywhere (map markers, station cards, station details, lists, filters).
- **Station services/amenities** (store, mechanic, motor oil, car oil, CR, air pump, etc.) are now a real per-station checklist, editable by the manager, shown publicly.
- **Public complaint system** — no account needed, generates a reference number, stored in `complaints`, with a basic honeypot field for spam protection.
- **LGU Analytics module** — price/station/complaint analytics with Chart.js bar/doughnut/pie/line charts, filterable by date range, barangay, fuel type, and complaint status.
- **Reports** — fuel price, station, and complaint reports, viewable on screen, printable, and exportable to CSV. *(PDF export isn't wired in — see "Known limitations" below.)*
- **Staff accounts + permissions** — managers create staff scoped to their own station only, with a permission checklist (`station_personnel.permissions`); enforced by a new `permission:` middleware.
- **Audit log** (`audit_logs`) records price updates, station changes, staff account changes, and complaint actions.
- **Area/barangay price view**, **cheapest-fuel finder**, and **nearest-station finder** (browser geolocation → haversine-distance backend endpoint) are new public pages.
- **Leaflet-based location picker** on the LGU's "Register Station" form — click or drag the marker, search a place name (via OpenStreetMap Nominatim), or use current location; lat/lng save automatically and the station appears on the public map immediately.

## Technology Stack

- **Backend:** PHP 8.2+, Laravel 11.x, MySQL/MariaDB, Laravel MVC
- **Frontend:** HTML5, CSS3, JavaScript ES6, Bootstrap 5.3, Bootstrap Icons, Chart.js (CDN)
- **Mapping:** Leaflet.js 1.9, OpenStreetMap, OpenStreetMap Nominatim (place search)
- **Dev environment:** XAMPP, Apache, MariaDB 10.x, VS Code, GitHub

## Installation

```bash
composer create-project laravel/laravel:^11.0 geogas-manfort
cd geogas-manfort
```

Copy this scaffold's `app/`, `bootstrap/app.php`, `database/`, `resources/views/`, `public/css/`, and `routes/web.php` into the new project, overwriting the defaults, then:

> ⚠️ **Applying an update from a later version?** Don't just extract the new zip on top of your existing project folder — a straight overlay only adds/overwrites files that exist in the new zip, it never deletes files that were removed or renamed since your last copy. Stale leftover files (especially in `database/migrations/`, where a renamed migration leaves the old one behind to run again and collide with the new one) are a common source of confusing errors after an update. Instead, delete your project's `app/`, `database/migrations/`, `resources/views/`, and `routes/` folders entirely before extracting the updated ones, or diff the two zips and remove anything that disappeared.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link   # required for complaint photo uploads to be publicly viewable
```

Edit `.env` for your database credentials (defaults assume `root`/no password on `127.0.0.1:3306`).

## Database Setup

```sql
CREATE DATABASE geogas_manfort;
```

```bash
php artisan migrate
php artisan db:seed
```

Seeded data (fuel types, stations, prices, availability, services, accounts, one sample complaint) is **development/test data only** — not real prices or real reports.

> ⚠️ **If you already ran `migrate` on an earlier version of this project** (before a schema update), running `migrate` again may silently do nothing — Laravel tracks completed migrations by filename, and some updates edit existing migration files in place rather than adding new ones. If you hit a "column not found" / "table not found" SQL error after pulling updated code, drop and rebuild the database instead:
> ```bash
> php artisan migrate:fresh --seed
> ```
> This is safe on a dev/capstone database with no real data to preserve.

## Running

```bash
php artisan serve
```

## Default Development Accounts

| Role | Email | Password |
|---|---|---|
| LGU Administrator | `lgu@geogasmanfort.test` | `password` |
| Manager — Tankulan Fuel Station | `manager0@geogasmanfort.test` | `password` |
| Staff — Tankulan Fuel Station | `staff0@geogasmanfort.test` | `password` |
| Manager — Manolo Fortich Petro Center | `manager1@geogasmanfort.test` | `password` |
| Staff — Manolo Fortich Petro Center | `staff1@geogasmanfort.test` | `password` |
| *(same pattern for `manager2`/`staff2`, `manager3`/`staff3`)* | | `password` |

Change these before any real/shared deployment.

## Map Configuration

Public map and the LGU's station-registration location picker are both centered on Manolo Fortich, Bukidnon (`8.3696, 124.8642`) via Leaflet.js + OpenStreetMap tiles — no API key required. Place-name search on the registration form uses OpenStreetMap's free Nominatim API from the browser (client-side fetch); for heavier production use, consider self-hosting Nominatim or adding a paid geocoder to respect Nominatim's usage policy.

## Known limitations / remaining issues

- **PDF export isn't implemented.** Reports support on-screen view, browser print, and CSV export (built with core PHP `fputcsv`, no new dependency). Wiring in true PDF generation is straightforward — add `barryvdh/laravel-dompdf` via Composer and render the existing report Blade views through it — but that requires Packagist access this sandbox doesn't have.
- **Distance/nearest-station ranking is computed in PHP** (haversine formula) rather than a spatial DB extension, which is fine at this station-count scale but wouldn't scale to a large multi-municipality dataset.
- **N+1-ish price lookups**: station listings call a small per-station subquery for "current price per fuel type" rather than one batched query. Fine for a few dozen stations; would want optimizing before a large statewide rollout.
- **Not run or tested here** — no PHP/Composer access in this sandbox. Please run through the checklist below on your machine before a defense/demo.

## Testing checklist

- [ ] Public: browse without login, search/filter stations, view map with availability filter, "Near Me"
- [ ] Compare prices by fuel type + sort; price-by-area view; cheapest-fuel finder
- [ ] Submit a complaint (with and without a photo), confirm reference number appears
- [ ] Manager: update prices/availability directly (confirm no approval step), edit services, view history
- [ ] Manager: create a staff account with limited permissions; confirm staff can't reach ungranted pages
- [ ] Staff: confirm they cannot manage other staff or reach another station's data
- [ ] LGU: register a new station via the map picker; confirm it appears on the public map immediately
- [ ] LGU: view analytics charts, filter by barangay/fuel type/date; manage a complaint's status
- [ ] LGU: generate each report, print, and export CSV
- [ ] Mobile: sidebar collapses, map stays usable, tables scroll horizontally

## Scope notes

Per the original capstone scope, this system intentionally does **not** include: AI/forecasting, online fuel purchasing, payment gateways, traffic monitoring, route navigation, weather integration, IoT/automated pump sync, or push notifications.
