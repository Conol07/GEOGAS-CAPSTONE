# GeoGas ManFort

**A Web-Based Fuel Price Monitoring and Geospatial Analysis System for Manolo Fortich, Bukidnon**

Public fuel-price and availability monitoring, a searchable/filterable station directory, a cheapest-fuel finder with automatic distance, a location-aware home dashboard, and a public complaint channel — backed by an LGU monitoring/analytics/reports/session-log console and a per-station Manager/Staff console with a direct (no-approval) price-update workflow.

This package is a **complete, installable Laravel 11 project** — the real framework skeleton (`artisan`, `vendor` dependencies via Composer, `config/`, `public/index.php`, `bootstrap/`) is included alongside the application code, so setup is the standard Laravel flow below. No merging of files or separate skeleton download required.

---

## Role structure

| Role | Login required | Can |
|---|---|---|
| **Public User** | No | View stations, prices, availability, services, logos; search; view the dashboard map; find cheapest fuel (with automatic distance); send a complaint |
| **LGU Admin** | Yes | Register stations (+ their manager account) with a map picker; monitor everything; view analytics; manage complaints; generate/print reports; view system-wide session logs. **Cannot approve/reject prices** — that workflow has been removed entirely |
| **Gasoline Station Manager** | Yes | Full control of their own station: update prices/availability directly, manage services, add custom fuel types, upload a logo/photo, create/manage staff with granular permissions, view reports and analytics, view their own and their staff's session logs — scoped to their station only |
| **Station Staff** | Yes | Whatever their manager grants: view dashboard, update prices, update availability, view price history/analytics, manage services, view/generate/print reports — never staff management, never another station |

## Feature highlights

- **Price approval workflow removed completely** — manager/staff price updates go live the moment they're saved.
- **Fuel prices are normalized and append-only** (`fuel_types` + `fuel_prices`) — every update is a new row, doubling as price history and powering price-change (increased/decreased/unchanged) indicators and Chart.js trend charts.
- **Custom fuel types**: a manager can add a fuel type their station offers that isn't in the system yet (with a specification, e.g. "10% Ethanol Blend"); it becomes a normal, database-driven fuel type used everywhere immediately.
- **Availability status** (`no_fuel` / `almost_empty` / `enough`) tracked per fuel type, shown on map markers, station cards, lists, and filters.
- **Station logo + photo upload** (upload/replace/remove, JPG/PNG/WEBP, validated) — shown throughout the public site with a clean placeholder fallback when missing.
- **Station services/amenities** checklist, editable by the manager, shown publicly.
- **Public complaint system** — no account needed, generates a reference number, honeypot spam protection.
- **LGU Analytics** — price/station/complaint charts (bar/doughnut/pie/line), filterable by date range, barangay, fuel type, complaint status.
- **Reports** — fuel price, station, complaint, and full per-station "station record" reports; on-screen, printable, and CSV-exportable.
- **Session logs** — login/logout/failed-login/account-settings-changed events for every account, with IP and a lightweight parsed browser/OS string. LGU sees everything; a manager sees their own plus their station's staff; staff see only their own.
- **Account Settings** (profile + password) replaces a bare "Change Password" link for every authenticated role.
- **Staff accounts + granular permissions**, enforced by a `permission:` middleware; a manager can never see or touch another station's data.
- **Audit log** records price/station/staff/complaint changes for LGU traceability.
- **Location-aware public dashboard**: optional "Activate My Location" powers a nearby-stations list and a small Leaflet map (with a "View Larger Map" modal) — the whole site works normally if location is declined.
- **Leaflet-based location picker** on the LGU's "Register Station" form (click/drag marker, place search via OpenStreetMap Nominatim, or use current location).

## Technology Stack

- **Backend:** PHP 8.2+, Laravel 11.x, MySQL/MariaDB, Laravel MVC
- **Frontend:** HTML5, CSS3, JavaScript ES6, Bootstrap 5.3, Bootstrap Icons, Chart.js (CDN)
- **Mapping:** Leaflet.js 1.9, OpenStreetMap, OpenStreetMap Nominatim (place search)
- **Dev environment:** XAMPP, Apache, MariaDB 10.x, VS Code, GitHub

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link   # required for station photos/logos and complaint evidence to be publicly viewable
```

Edit `.env` if your XAMPP MySQL differs from the defaults (`root` / no password on `127.0.0.1:3306`) — see the `DB_*` lines.

> ⚠️ **Applying a later update?** Don't extract a new zip on top of this folder without checking for removed files first — a straight overlay only adds/overwrites what's in the new zip, it never deletes files that were removed or renamed since your last copy (this has caused stale-migration errors before). Delete `app/`, `database/migrations/`, `resources/views/`, and `routes/web.php` before extracting an update, or diff the two zips and remove anything that disappeared. `vendor/`, `artisan`, and `config/` won't be affected by application updates — no need to touch them.

## Database Setup

```sql
CREATE DATABASE geogas_manfort;
```

```bash
php artisan migrate
php artisan db:seed
```

Seeded data (fuel types, stations, prices, availability, services, accounts, one sample complaint) is **development/test data only** — not real prices or real reports.

> ⚠️ If you ever hit "table already exists" or "column not found" after pulling updated code, your database is out of sync with the current migrations. On a dev/capstone database with no real data to preserve, rebuild cleanly:
> ```bash
> php artisan migrate:fresh --seed
> ```

## Running

```bash
php artisan serve
```

Or point an Apache vhost at this project's `public/` folder if you'd rather run it through XAMPP's Apache directly.

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

The public dashboard map and the LGU's station-registration location picker are both centered on Manolo Fortich, Bukidnon (`8.3696, 124.8642`) via Leaflet.js + OpenStreetMap tiles — no API key required. Place-name search on the registration form uses OpenStreetMap's free Nominatim API from the browser; for heavier production use, consider self-hosting Nominatim or a paid geocoder to respect its usage policy.

## Known limitations / remaining issues

- **PDF export isn't implemented.** Reports support on-screen view, browser print, and CSV export (`fputcsv`, no extra dependency). Adding true PDF generation is a small lift — `composer require barryvdh/laravel-dompdf` and render the existing report Blade views through it.
- **Distance is computed with the haversine formula in PHP/JS**, not a spatial DB extension — fine at this station-count scale, not meant for a large multi-municipality dataset.
- **N+1-ish price lookups**: station listings run a small per-station subquery for "current price per fuel type" rather than one batched query — fine for a few dozen stations, worth optimizing before a large rollout.
- **Session-log device parsing** is simple substring matching on the user-agent string, not a full UA-parsing library — it'll misidentify uncommon browsers.
- **Not run or tested in the environment that built it** — this was assembled and merged with the real Laravel skeleton in a sandbox without a live PHP/MySQL runtime to execute it against. Please run through the checklist below on your machine before a defense/demo.

## Testing checklist

- [ ] `composer install` completes and `php artisan serve` boots without errors
- [ ] Public: browse without login, search bar, station page filters, dashboard map + "Activate My Location", cheapest-fuel automatic distance
- [ ] Submit a complaint (with and without a photo), confirm reference number appears
- [ ] Manager: update prices/availability directly, add a custom fuel type, upload/replace/remove a logo and photo, edit services, view analytics
- [ ] Manager: create a staff account with limited permissions; confirm staff can't reach ungranted pages
- [ ] Staff: confirm they cannot manage other staff, reach another station's data, or see another staff member's session logs
- [ ] LGU: register a new station via the map picker; confirm it appears on the public dashboard immediately
- [ ] LGU: view analytics charts; manage a complaint's status; generate/print each report including the per-station "Station Record"
- [ ] LGU + Manager + Staff: view session logs, confirm login/logout/failed-login entries appear correctly scoped
- [ ] Account Settings: update profile info and change password for each role
- [ ] Mobile: sidebar collapses, bottom tab bar works, map stays usable, tables scroll horizontally

## Scope notes

Per the original capstone scope, this system intentionally does **not** include: AI/forecasting, online fuel purchasing, payment gateways, traffic monitoring, route navigation, weather integration, IoT/automated pump sync, or push notifications.
