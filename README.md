# GeoGas ManFort

**A Web-Based Fuel Price Monitoring and Geospatial Analysis System for Manolo Fortich, Bukidnon**

A capstone-style Laravel application for centrally tracking and verifying fuel prices from participating gasoline stations in Barangay Tankulan, Manolo Fortich, Bukidnon — with public price comparison, an interactive Leaflet/OpenStreetMap view, and an administrator verification workflow.

> ⚠️ **Note on this scaffold:** this repository contains the full application source (migrations, models, controllers, middleware, routes, Blade views, seeders) hand-written to the Laravel 11 file layout. It was generated in a sandboxed environment without outbound access to Packagist, so `vendor/` and the Laravel skeleton files (`artisan`, `bootstrap/cache`, `public/index.php`, etc.) are **not included**. Follow the setup steps below on your own machine — `composer create-project` will pull in the missing skeleton, and this app code drops in cleanly on top of it.

---

## Features

- **Public module (no account required):** browse participating stations, search/filter, view station details, compare fuel prices side by side, and explore an interactive map.
- **Station personnel module:** login, station dashboard, submit fuel price updates, view submission status and history — scoped strictly to their own assigned station.
- **Administrator module:** dashboard with key metrics, user management, station management/registration, fuel price verification (approve/reject with audit trail), and printable reports.
- **Verification workflow:** `pending → approved/rejected`, with the publicly displayed price only ever reflecting the latest **approved** submission.
- **Interactive map:** Leaflet.js + OpenStreetMap, centered on Manolo Fortich, with station markers and popups showing the latest verified prices.
- **Role-based access control** enforced via middleware, with station personnel unable to modify another station's records.

## Technology Stack

- **Backend:** PHP 8.2+, Laravel 11.x, MySQL/MariaDB, Laravel MVC
- **Frontend:** HTML5, CSS3, JavaScript ES6, Bootstrap 5.3
- **Mapping:** Leaflet.js 1.9, OpenStreetMap
- **Dev environment:** XAMPP, Apache, MariaDB 10.x, VS Code, GitHub

## System Requirements

- PHP 8.2+
- Composer 2.x
- MySQL/MariaDB (via XAMPP or standalone)
- Node.js (optional — only needed if you later add an asset build step; this scaffold uses CDN-hosted Bootstrap/Leaflet, so it's not required to run)

## Installation

1. **Create a fresh Laravel 11 skeleton**, then merge this scaffold's files into it:

   ```bash
   composer create-project laravel/laravel:^11.0 geogas-manfort
   cd geogas-manfort
   ```

   Copy the contents of this scaffold's `app/`, `bootstrap/app.php`, `database/`, `resources/views/`, and `routes/web.php` into the newly created project, overwriting the defaults.

2. **Install dependencies** (already present from `create-project`, but if you copied `composer.json` over, re-run):

   ```bash
   composer install
   ```

3. **Environment configuration:**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Edit `.env` and confirm your database credentials match your XAMPP/MariaDB setup (defaults assume `root` with no password on `127.0.0.1:3306`).

## Database Setup

1. In phpMyAdmin (or the MySQL CLI), create a database:

   ```sql
   CREATE DATABASE geogas_manfort;
   ```

2. Run migrations:

   ```bash
   php artisan migrate
   ```

3. Seed development/test data (sample stations, an admin account, and station-personnel accounts with sample pending/approved prices):

   ```bash
   php artisan db:seed
   ```

   > Seeded fuel prices are clearly **development/test data only** — they are not real current fuel prices.

## Running the Development Server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000`. (If instead running through XAMPP/Apache, point your vhost's document root at this project's `public/` folder.)

## Default Development Accounts

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@geogasmanfort.test` | `password` |
| Station Personnel (Station 1) | `staff0@geogasmanfort.test` | `password` |
| Station Personnel (Station 2) | `staff1@geogasmanfort.test` | `password` |
| Station Personnel (Station 3) | `staff2@geogasmanfort.test` | `password` |

Change these credentials before any real/shared deployment.

## User Roles

- **General User** — no login required; browse, search, compare, and view the map.
- **Station Personnel** — logs in; manages only their own assigned station's fuel price submissions.
- **System Administrator** — full control: user/station management, price verification, reports.

## Map Configuration

The map is centered on Manolo Fortich, Bukidnon (`8.3696, 124.8642`) using Leaflet.js with OpenStreetMap tiles (no API key required). Station coordinates are stored as `latitude`/`longitude` on `gasoline_stations` and are editable from the admin station form.

## Project Structure (this scaffold's key paths)

```
app/Models/                     User, GasolineStation, StationPersonnel, FuelPrice
app/Http/Controllers/           Auth, Admin, StationPersonnel, PublicController
app/Http/Middleware/            EnsureUserHasRole, EnsureOwnsStation
database/migrations/            users, gasoline_stations, station_personnel, fuel_prices
database/seeders/                DatabaseSeeder.php (sample dev data)
resources/views/                 layouts, auth, public, station, admin
routes/web.php                   all application routes
```

## Troubleshooting

- **"Class not found" errors after copying files:** run `composer dump-autoload`.
- **419 Page Expired on login:** confirm `APP_URL` in `.env` matches the URL you're actually browsing to, and that cookies aren't blocked.
- **Map tiles not loading:** check your internet connection — OpenStreetMap tiles and the Leaflet/Bootstrap CDN assets are loaded from the CDN at runtime.
- **"SQLSTATE[HY000] [1049] Unknown database":** make sure you created the `geogas_manfort` database before running `php artisan migrate`.
- **Station personnel seeing a 403 on their own station's routes:** confirm their `users.status` is `active` and that a matching `station_personnel` row links their `user_id` to a `gasoline_stations.id`.

## Scope Notes

Per the project scope, this system intentionally does **not** include: fuel price forecasting/AI prediction, route navigation, traffic monitoring, IoT/automated pump sync, weather services, push notifications, online purchasing, or payment gateways.
