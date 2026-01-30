# INMS Mini-Module (Laravel + Inertia + Vue)

A polished Laravel + Inertia + Vue newsroom workflow with a lightweight GIS election map prototype.

## Stack
- Laravel 12 + Breeze (Inertia + Vue)
- Tailwind CSS v4
- Shadcn-inspired Vue components
- Leaflet.js for GIS map
- MySQL

## Features
- Authentication (Breeze)
- Role-based access: Reporter, Editor, Admin
- News workflow: Draft -> Review -> Approved
- Role management (create roles + permissions)
- User management (filters, pagination, deactivate)
- REST API with Sanctum token auth
- GIS Map with Ethiopia boundary + sample region polygons

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Update `.env` with MySQL credentials:

```
DB_CONNECTION=mysql
DB_DATABASE=inms
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations + seed demo users:

```bash
php artisan migrate --seed
```

Start the app:

```bash
php artisan serve
npm run dev
```

## Demo Accounts
- Admin: `admin@inms.test` / `password`
- Editor: `editor@inms.test` / `password`
- Reporter: `reporter@inms.test` / `password`

## REST API
Base path: `/api`

1) Login for token:

```bash
POST /api/login
{
  "email": "admin@inms.test",
  "password": "password"
}
```

Use the returned token:

```
Authorization: Bearer <token>
```

Endpoints:
- `GET /api/articles`
- `POST /api/articles`
- `GET /api/articles/{id}`
- `PUT /api/articles/{id}`
- `PATCH /api/articles/{id}/status`
- `POST /api/logout`

## Notes
- GIS map uses official Ethiopia admin boundary GeoJSON files (regions, zones, woredas) and generated metrics.
- Source files are downloaded into `storage/app/geo/raw`, then processed into `public/data/ethiopia-admin*.geojson` via `npm run build:map-data`.
- Region population estimates are mapped to current boundaries and lower levels are allocated by area weighting (modeled). Labels note this.

Data sources:
- Ethiopia admin boundaries (ADM1/ADM2/ADM3) from EthioSATHub (ESS).
- Regional population estimates (2023) from Ethiopian Statistical Service summaries (compiled in `scripts/build-ethiopia-map-data.js`).

Role rules:
- Reporter: create/edit drafts, submit to review
- Editor: edit drafts/review, approve review
- Admin: full control

### Map data build

```bash
npm run build:map-data
```

If the raw boundary files are missing, download them first:

```bash
./scripts/download-ethiopia-geo.sh
npm run build:map-data
```

This rebuilds:
- `public/data/ethiopia-admin1.geojson`
- `public/data/ethiopia-admin2.geojson`
- `public/data/ethiopia-admin3.geojson`

## Screenshots / Demo
Add screenshots or demo link here.
