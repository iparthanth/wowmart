# WowMart Admin

The admin panel for **WowMart**, a multi-vendor marketplace. Laravel 13 · PHP 8.4 · MySQL 8.4.

A separate codebase from the storefront. The two applications share **one database**,
and this app owns its schema: the storefront reads and writes rows but never changes
table structure. One owner means the two can never disagree about what a table looks like.

## Brand

| | |
|---|---|
| Logo | `public/images/brand/wowmart-logo.svg` (light backgrounds) |
| Logo, reversed | `public/images/brand/wowmart-logo-light.svg` (dark backgrounds) |
| Mark / favicon | `public/images/brand/wowmart-mark.svg`, `public/favicon.svg` |
| Tangerine | `#F2622E` — primary |
| Ink navy | `#14213D` — text, dark surfaces |
| Amber | `#FFB627` — the spark, accents only |

The wordmark is drawn as strokes rather than set in a font, so it renders identically
everywhere with nothing to install.

## Environment

Everything lives on `D:` — the `C:` drive on this machine is near capacity.

| Component | Where | Notes |
|---|---|---|
| PHP 8.4.10 | on PATH | runs the app (`php artisan serve`) |
| MySQL 8.4.3 | `D:\mysql` | the **only** database server |
| XAMPP | `D:\xampp` | **Apache + phpMyAdmin only** |
| Composer | on PATH | cache in `D:\.composer-cache` |

**Never start XAMPP's MySQL module.** It is MariaDB 10.4 and would fight MySQL 8.4 for
port 3306. XAMPP's PHP (8.2) is also too old to run this app, which needs 8.3+.

### Run it

```
D:\mysql\bin\mysqld.exe --defaults-file=D:/mysql/my.ini --console
php artisan migrate:fresh --seed
php artisan serve --port=8124
```

Admin sign-in: `http://127.0.0.1:8124/login` — `admin@wowmart.test` / `password`.
That account exists only in local seed data.

### Viewing the data

| Want to see | Where |
|---|---|
| Tables and rows | `http://localhost/phpmyadmin` (start XAMPP Apache first) |
| The files on disk | `D:\mysql\data\wowmart\` — one `.ibd` file per table |
| The data folder path | `SELECT @@datadir;` |

Database `wowmart`, app user `wowmart`. Passwords are kept outside this repository.

## Schema

82 tables across 31 additive migrations, grouped by domain: catalog, sales, people, shipping,
configuration and communication. Every migration is numbered `2026_MM_DD_...` so the schema can
be rebuilt from an empty database with one command (`php artisan migrate`).

## Seed data

Deterministic — no random numbers — so a fresh seed always gives the same result:

- 42 products (32 in-house, 10 seller), 1 sold out, 2 low on stock
- 11 categories, 4 brands, 5 sellers, 3 customers
- 3 coupons, one of each type · 1 running flash deal
- 8 orders covering all six delivery states

### Before any public deployment

The 41 product photos in `public/uploads/catalog/` were captured from a public storefront
for local development. **They are not WowMart's to publish.** Replace them with your own
photography, or licensed images, before this is deployed anywhere public.

## API documentation

`docs/openapi.json` is an OpenAPI 3.1 spec generated from the live route list
(`php scripts/generate-openapi.php`) — import it into Postman or any OpenAPI viewer for a
clickable catalog of all `/api/v1` endpoints. `docs/postman_collection.json` is the same
catalog as a ready-to-run Postman collection.

## Archived modules

To keep the panel focused, only **Dashboard, Products, Sales, Reports, Support and
Staffs** (plus Uploaded Files, which products depend on) live in this folder.
Every other module — POS, customers, sellers, refunds, delivery boys, marketing
(flash deals, coupons, pop-ups, newsletters), notifications, blog, pages, website
setup, settings, currencies, taxes, pickup points, shipping, languages, affiliate,
OTP, payment gateways and system — was moved, with its routes, code, views, tests,
seeders and migrations, to `D:\wowmart_archive\admin` under the same paths.
See `D:\wowmart_archive\README.md` to bring one back.
