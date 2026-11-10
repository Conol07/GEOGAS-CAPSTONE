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

## What's new in v12

- **GIS console on Home** (replaces the old "Participating Stations" card and the larger-map modal). Modes: station markers, price heatmap, combined. Filters: fuel type, price level, availability, barangay; colour markers by availability or price level; highlight lowest / highest / closest-to-average; optional barangay area circles; locate-me and reset. Data comes from `GET /gis/data` (public JSON: location, current prices, availability only).
  - Price levels are **relative quartile bands** for the selected fuel (not official classifications). Heat intensity is min-max scaled price. Stations without valid coordinates or a price for the selected fuel are excluded and counted in the summary.
  - Barangay "areas" are centroid circles around stations, an approximation rather than real boundaries.
- **LGU GIS page**: `LGU > Fuel Monitoring > GIS Map / Heatmap` (`/lgu/gis`), with print.
- **Complaint image evidence** (optional): JPG/PNG/WEBP up to 4 MB, preview/remove/replace, validated server-side, stored on the private disk and served only to LGU admins at `/lgu/complaints/{id}/evidence`.
- **LGU station detail page** (`/lgu/stations/{id}`): profile, map, manager and staff accounts (no credentials), current prices, price history, complaints, activity, activate/deactivate.
- **Collapsible sidebar** for LGU, Manager and Staff: grouped categories, active group stays open, mobile menu, desktop icon-only mode, separate Log Out. Items are pruned by role/permission and enforced by route middleware.

No new migrations in v12. If you are upgrading by copying files over an old install, **do not overlay zips** (stale migrations remain); extract into a fresh folder and run `php artisan migrate:fresh --seed`.

## v13 — safeguards

All tunable in `.env` (see `config/geogas.php`):

- **Login rate limiting**: 5 failed attempts per email+IP lock that login for 15 minutes (`GEOGAS_LOGIN_*`); lockouts appear in Session Logs. Complaint submissions: 5 per hour per IP.
- **Price caps**: prices must be between ₱20 and ₱200 (`GEOGAS_PRICE_MIN/MAX`). A change of more than 10% (`GEOGAS_PRICE_JUMP_PERCENT`) is held until the manager ticks a confirmation.
- **Stale-price flags**: prices older than 72 h (`GEOGAS_STALE_HOURS`) show "Price may be outdated" on station cards, Cheapest Fuel, station pages and map popups.
- **Reminders**: `php artisan geogas:remind-stale` (`--dry-run` to preview) emails managers whose station has not updated for 48 h (`GEOGAS_REMINDER_HOURS`), at most once a day per station. It is scheduled daily at 08:00 and needs the scheduler (`* * * * * php artisan schedule:run`) and a real `MAIL_*` setting; with `MAIL_MAILER=log` emails go to `storage/logs`. Managers also see a banner on their dashboard.
- **"Prices unchanged — confirm"** button on the price form refreshes the timestamp without retyping prices (recorded in history).
No new migrations.

## More demo stations

`php artisan db:seed --class=MoreStationsSeeder` adds 22 demo stations across Manolo Fortich barangays (Damilag, Dalirig, Maluko, Sankanan, Dahilayan, Lunocan, Santiago, Lingion, Alae, Tankulan, Diclum, Guilang-guilang, Kalugmanan, Mantibugao, Minsuro, San Miguel, Santo Niño, Ticala, Lindaban) without touching existing data; it is also run by `migrate:fresh --seed`. Their coordinates are approximate placeholders. Managers: `manager15@geogasmanfort.test` and up (check the Users page for the exact emails), password `password`. Register real stations from the LGU page and pin them on the map.

## v15 — heatmap switches, logo pins

- **Heatmap on/off for everyone**: a Heatmap switch (and a Stations switch) sits on the public map and the LGU GIS page. Filters are tucked under a Filters button.
- **LGU control** (`LGU > Fuel Monitoring > GIS Map / Heatmap > Public Heatmap Settings`): "Heatmap available to the public" and "Automatic heatmap" (map opens with the heatmap already drawn from live prices). Stored in the new `settings` table. **Run `php artisan migrate`** (no need for `migrate:fresh`).
- **Station logo inside map pins**; stations without a logo show the pump icon.
- News: LGU posts under Community > News & Announcements; published items appear on Home and `/news`. `MoreStationsSeeder` adds two sample announcements.

## v17 - Philippine fuel news

News now has a **scope**: *Manolo Fortich (LGU)* or *Philippines (national)*.

- **LGU > News > Add News**: pick the news type. National items need a source name and a link to the original article; write a short summary in your own words (do not paste the article).
- **Public**: the News page has All / Manolo Fortich / Philippines tabs; the home page shows "Latest from the LGU" and "Fuel News in the Philippines"; national items carry a Philippines badge and a "Read the original article" link.
- **Optional importer**: `php artisan geogas:import-national-news` reads RSS/Atom feeds listed in `.env` (`GEOGAS_NEWS_FEEDS="Name|https://feed-url,Name2|https://..."`), keeps only headlines matching `GEOGAS_NEWS_KEYWORDS`, and saves them as **drafts** (headline, 300-character snippet, link). The LGU reviews, rewrites and publishes. It is empty by default; add feed URLs you have checked yourself (use `--dry-run` first, or `--file=sample.xml` to test). It runs daily at 07:00 if the scheduler is running.
- Upgrade: `php artisan migrate` then (optional) `php artisan db:seed --class=MoreStationsSeeder` for one explainer item. No station, admin or price data is touched.

National news starter items only (no stations): `php artisan db:seed --class=NationalNewsSeeder`. The home page now always shows the Philippines news section (with a message when empty).

### Automatic Philippine fuel news (v17)
1. In `.env` set `GEOGAS_NEWS_FEEDS` (see `.env.example`), then `php artisan config:clear`.
2. Test: `php artisan geogas:import-national-news --dry-run`.
3. LGU > News now has a **Fetch Philippine Fuel News** button (only shown when feeds are set).
4. Hands-free on Windows/XAMPP: Task Scheduler > new task, repeat every 1 minute, program `C:\xampp3\php\php.exe`, arguments `artisan schedule:run`, start in the project folder. The importer then runs daily at 07:00.
5. Default is **drafts for LGU review**. `GEOGAS_NEWS_AUTO_PUBLISH=true` publishes headline + 300-character snippet + source link immediately.
DOE does not offer a news feed that I could confirm, so the public page links to doe.gov.ph and the LGU can post DOE advisories by hand.

## v17 - Trip cost estimator, dark mode, live updates

- **Distance and travel time** (inside the GIS map): choose "Your Vehicle" (motorcycle, tricycle, car, van/SUV/pickup, jeepney, truck/bus) in the map toolbar and tap My Location. Each station popup then shows kilometers (straight line x 1.3) and estimated minutes (35 km/h motorcycle, 30 car/van, 25 tricycle/jeepney/truck), and the side panel lists the 3 nearest stations. The choice is remembered in the browser.
- **Dark / light mode**: moon/sun button in the navbar. Follows the device setting until you choose; saved in `localStorage` (`ggTheme`). Printing always uses light.
- **Live updates**: `public/js/live.js` refreshes regions marked `data-live="60"` (home price overview and latest updates, LGU and station dashboard cards) and the GIS map every 60 s while the tab is visible. A "Live" badge lets users pause. To make another region live, give it a unique `id` and `data-live="60"`.
- No migration needed. After copying files run `php artisan optimize:clear`.
