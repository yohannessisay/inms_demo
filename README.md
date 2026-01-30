# INMS Mini-Module (Laravel + Inertia + Vue)

A complete newsroom workflow and GIS election map prototype built with Laravel 12, Inertia, and Vue. The app includes role-based access control, a structured article approval pipeline, user and role management, and a public Ethiopia election map with region/zone/woreda details.

## Stack
- Laravel 12 + Breeze (Inertia + Vue)
- Tailwind CSS v4
- Shadcn-inspired Vue components
- Leaflet.js (GIS map)
- MySQL

## Key Features
- Authentication (Laravel Breeze)
- Role-based access control (Reporter, Editor, Admin + custom roles)
- News workflow: Draft -> Review -> Approved
- Role management (create roles + permissions)
- User management (filters, pagination, activate/deactivate, edit in drawer)
- REST API with Sanctum token auth
- Public GIS map with Ethiopia regions, zones, and woredas

## System Requirements
- PHP 8.4+
- Composer
- Node.js 18+ (or 20+)
- MySQL 8+

## Setup

1) Install backend and frontend dependencies:

```bash
composer install
npm install
```

2) Copy environment file and generate app key:

```bash
cp .env.example .env
php artisan key:generate
```

3) Update `.env` with MySQL settings:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inms
DB_USERNAME=root
DB_PASSWORD=
```

4) Run migrations + seed demo users:

```bash
php artisan migrate --seed
```

## Run the App

This project uses `concurrently` so you can run backend and frontend together:

```bash
npm run dev
```

If you want to run them separately:

```bash
php artisan serve
npm run dev
```

## Demo Accounts
- Admin: `admin@inms.test` / `password`
- Editor: `editor@inms.test` / `password`
- Reporter: `reporter@inms.test` / `password`

## Newsroom Workflow
- Reporters create drafts and submit for review.
- Editors review, edit, and approve submissions.
- Admins have full control across articles, users, and roles.

## Role Management
- Add new roles and assign permissions under **Roles**.
- Built-in permissions:
  - articles.view_all
  - articles.create
  - articles.edit
  - articles.edit_all
  - articles.review
  - articles.approve
  - articles.manage
  - users.manage
  - roles.manage

## User Management
- Filter and paginate users.
- Edit in a drawer (no full-page reload).
- Deactivate/reactivate users safely (soft delete supported for safety).

## GIS Map
The map is public and uses Ethiopia admin boundaries (ADM1/ADM2/ADM3).
Hover shows Region/Zone/Woreda and metrics; click focuses a region and pins a detailed label.

### Map Data Build
GeoJSON data is prebuilt in `public/data`, but you can rebuild:

```bash
npm run build:map-data
```

If raw boundary files are missing:

```bash
./scripts/download-ethiopia-geo.sh
npm run build:map-data
```

This regenerates:
- `public/data/ethiopia-admin1.geojson`
- `public/data/ethiopia-admin2.geojson`
- `public/data/ethiopia-admin3.geojson`

## REST API
Base path: `/api`

### Login (token)
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

## Data Notes
- Ethiopia admin boundaries (ADM1/ADM2/ADM3) from EthioSATHub (ESS).
- Regional population estimates (2023) compiled in `scripts/build-ethiopia-map-data.js`.
- Lower-level metrics are modeled allocations based on area weighting.

## Troubleshooting
- **Composer PHP version error:** use PHP 8.4+ and ensure it is first in your PATH.
- **Map not loading:** confirm `public/data/ethiopia-admin*.geojson` exists or rebuild map data.
- **Permissions issues:** ensure you are logged in with an admin role or assign permissions in Roles.

## Screenshots / Demo
Add screenshots or a demo link here.
