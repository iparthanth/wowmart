# WowMart Admin

WowMart is a multi-vendor online shop, and this repository is its **admin panel**: the
private back office where the shop owner manages products, follows orders from
"pending" to "delivered", reads sales and stock reports, answers customer questions
and decides which staff can see what.

I built it during my industrial attachment at **Authentic for Technology**
(Chittagong). The layout and features follow a real production admin panel, but none of
that product's source code is used: the whole panel was written fresh in Laravel.

**Stack:** Laravel 13 · PHP 8.3+ · MySQL 8 (or MariaDB 10.4+) · plain CSS, no build step.

---

## Features

**Dashboard**
- A "needs attention" list: pending orders, unpaid orders, sold-out products, low-stock products and products without an image, each linking to the filtered list where you fix it
- Totals for products, customers, sellers and order value
- A 14-day sales chart, with a table view of the same numbers
- The latest orders at a glance

**Products**
- Add, edit, duplicate and delete products, with price, discount window, stock, low-stock warning, gallery, video, SEO, shipping, warranty and refund settings in one form
- Search and filter the product list by owner, category, stock level, status and missing image
- One-click switches for published, featured and today's deal
- Categories as a tree (parent, level and display order), brands with logos, attributes with their values, colours, warranties, and reusable refund and warranty notes
- Product reviews: hide or show a review, and the product's rating recalculates itself
- Anything still in use (a category with products, a brand on products) can't be deleted by mistake

**Sales**
- All orders, plus in-house, seller, pickup-point and unpaid order lists
- Filter by delivery status, payment status, date range or customer
- Move an order through its six states: pending → confirmed → picked up → on the way → delivered, or cancelled
- Cancelling an order puts the stock back exactly once, even on a double click
- Mark orders paid or unpaid; a delivered cash-on-delivery order is marked paid automatically

**Media**
- Upload images and PDFs once, then pick them anywhere a product, category or brand needs a picture
- Files are checked by their content, not their name, and a file that's still in use can't be deleted

**Reports**
- Earnings, in-house sales, seller sales, stock, wishlists, user searches, commission history and wallet recharges
- Date and category filters, with a CSV download for every report

**Support**
- Customer support tickets with replies and open / answered / closed status
- Product conversations between customers and sellers, product questions and contact-form messages

**Staff and permissions**
- Staff accounts, each with a role
- Roles decide which sections a staff member can open; the menu hides everything else

**Security**
- Admin-only sign-in, with the same message for every failed attempt and a one-minute lock after five tries
- Every page and API endpoint checks the user's role

**REST API**
- Every section is also available at `/api/v1`, protected by login tokens that expire after 24 hours
- A ready-to-import Postman collection and an OpenAPI 3.1 description

---

## Technologies Used

| Technology | What it's used for |
|---|---|
| **PHP 8.3+** | The language the whole panel is written in |
| **Laravel 13** | The web framework: routing, controllers, Eloquent database models, validation, migrations and seeders |
| **MySQL 8** (or MariaDB 10.4+) | The database |
| **Blade** | Laravel's template engine, used for every screen |
| **HTML and CSS** | Plain, hand-written CSS in a single file; no CSS framework and no build step |
| **SVG** | The WowMart logo and the dashboard's sales chart |
| **Laravel Sanctum** | Login tokens for the REST API |
| **PHPUnit 12** | The automated tests (273 tests on an in-memory SQLite database) |
| **Composer** | Installs Laravel and the other PHP libraries |
| **Postman** | Trying out and documenting the API |
| **phpMyAdmin / XAMPP** | Creating and importing the database |
| **Google Fonts** | Sora (headings), Hanken Grotesk (text), JetBrains Mono (codes and numbers), Hind Siliguri (the ৳ sign) |
| **Git and GitHub** | Version control and hosting the code |

---

## How to run it

You need four things installed:

| Tool | Version | Notes |
|---|---|---|
| **PHP** | 8.3 or newer | Laravel 13 needs it. **XAMPP's own PHP (8.2) is too old** — install PHP separately from [windows.php.net](https://windows.php.net/download/) or with [php.new](https://php.new). In `php.ini`, make sure `pdo_mysql`, `mbstring`, `openssl` and `fileinfo` are enabled (and `pdo_sqlite` if you want to run the tests). |
| **Composer** | 2.x | [getcomposer.org](https://getcomposer.org/download/) |
| **MySQL + phpMyAdmin** | MySQL 8 or MariaDB 10.4+ | The easiest way is **XAMPP**: start only **Apache** and **MySQL** from the XAMPP Control Panel. |
| **Git** | any | to download the code |

Check PHP with `php -v` — it must say 8.3 or higher.

### Step 1 — Download the code

```bash
git clone https://github.com/iparthanth/wowmart.git
cd wowmart
composer install
```

`composer install` downloads Laravel and the other libraries into a `vendor` folder.
It can take a few minutes the first time.

### Step 2 — Create the database and import the SQL file

1. Open **phpMyAdmin**: <http://localhost/phpmyadmin>
2. Click **New** in the left panel.
3. Type the database name **`wowmart`**, choose collation **`utf8mb4_unicode_ci`**, and click **Create**.
4. With `wowmart` selected on the left, open the **Import** tab.
5. Click **Choose File** and pick **`database/sql/wowmart.sql`** from the project folder.
6. Scroll down and click **Import** (or **Go**).

You should see a green "Import has been successfully finished" message and 34 tables
on the left. The file already contains the demo data: 42 products, 29 brands, 13 orders,
reviews, report data, support tickets and two demo staff accounts.

### Step 3 — Tell the app how to reach the database

Make a copy of the example settings file:

```bash
copy .env.example .env
```

(On macOS or Linux use `cp .env.example .env`.)

Open `.env` in any text editor and check the database lines. They are already set up for
a standard XAMPP install, where the MySQL user is `root` with no password:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wowmart
DB_USERNAME=root
DB_PASSWORD=
```

If your MySQL uses a different user or password, change `DB_USERNAME` and `DB_PASSWORD`.

Then generate the app's secret key (needed for logins and sessions):

```bash
php artisan key:generate
```

### Step 4 — Start the panel

```bash
php artisan serve| php artisan serve --port=8124
```

Leave this window open, then go to **<http://127.0.0.1:8000/login>** or **<http://127.0.0.1:8124/login>** in your browser and sign in with the demo admin account:

| Email | Password |
|---|---|
| `admin@wowmart.test` | `password` |

That's it. To stop the server, press `Ctrl + C` in that window.

To see staff permissions in action, sign out and sign in as one of the demo staff members.
Each one only sees the sections their role opens:

| Staff member | Email | Password | Role | Can open |
|---|---|---|---|---|
| Sadia Rahman | `sadia@staff.wowmart.test` | `sadiastaff` | Order manager | Sales, reports, support |
| Imran Hossain | `imran@staff.wowmart.test` | `imranstaff` | Catalogue editor | Products, media |

### Another way to fill the database (instead of Step 2)

If you prefer not to use the SQL file, create an empty `wowmart` database, finish Step 3,
and run:

```bash
php artisan migrate --seed
```

This builds all the tables from the migration files and adds the same demo data.

---

## Trying the API in Postman

1. Open Postman → **Import** → choose **`docs/postman_collection.json`**.
2. The collection already points at `http://127.0.0.1:8124/api/v1` (the `baseUrl`
   variable). Keep the server from Step 4 running.
3. Open **Authentication → Sign in** and click **Send**. It signs in with the demo admin
   and saves the login token for the rest of the collection automatically.
4. Now send any other request — products, orders, reports, and so on.

`docs/openapi.json` describes the same API in OpenAPI 3.1 format, if you prefer
Swagger UI or another viewer. Tokens expire after 24 hours; just sign in again.

---

## Database Architecture

The panel uses one **MySQL** database, `wowmart`, with **34 tables** (InnoDB, `utf8mb4_unicode_ci`).
Every table is defined by a migration in `database/migrations/`, the demo data comes from
`database/seeders/`, and `database/sql/wowmart.sql` is the same database as a ready-to-import file.

### Diagram

**[Open the interactive diagram in Mermaid Live Editor](https://mermaid.live/edit#pako:eNqlVsuO4yAQ_BXEOfMDOa_2MpeV9hrJYkwngwYDC81EkZN_Xx7Gj5g8NPHFNlQ3RVNddk9bzYFuKdhfgh0s63aKhMtqCY6cz29vuk8vjQHbCeeEVo5syY4GrEK3owu8TnjvwA4g8Q2KoCYO2X5fwHk-gc_EfWqTwQ6kBEv0UbkrZKZhrOa-xQyeo1qGcNBWTAxmIxFsmAWFRO9vRyySd0yogjmVmBFxqa7BpNNECofAiVAl6CNUidcXSVMzTt5IzW5g8dN3H4oJeYtMCU5FZ7GOa96xjOcxceNQt19D5eNjFR9PH74FHDMwPy92uDiiOfZoBUL9JLXlRSFGsnZCDRMD0_TWcMCw8YxutQov08lPXPWUeBHhtOSE1Wk4b4y22KBov6DoysCU_hpQjWssGFlUMDzHBPMF-_wSLxGEKDj58z4NObRCHRK0wZOBqCbeCbXJbRNuqTM2pPXhpDqwhV_Jlxo0JP29TgpdVE0avcxb9SlGinWwil04Qf80kSlqkTG3_302cSgVp555xXJUxc_SxvHS_LW51Li1ibFLG9Ed7jONF4dWdEwSrwQ2xooWrjh4G20rN-pigzPrebzFbH7P1m4wrJ_Jo7jQU9F7EbRy6-yKOz2xvSGgch7_8LRU7-BOLyW9pRjLMOxqsd7gZi-JO36eV4McZPiwBnUGf0Dv1pI6JImiRibXjEZ_fEws4yu7vVd1H34MRCn9nNVM45e6wb5UqnkxqvlHp368zFVgfcGYblIY3dBgzcFuefid6uM3G4K26XZHOeyZl7ijl4BhHvXfk2rpFq2HDfWGh24efr7y4OU_i-kDhg)** 

![image](https://github.com/iparthanth/wowmart/blob/70e39476a608c621c150a4416616d654e5a7588e/wowmart-erd.svg)

### Tables by area

| Area | Tables | What they hold |
|---|---|---|
| **Catalogue** (13) | `products`, `product_categories`, `product_upload`, `product_stocks`, `categories`, `brands`, `attributes`, `attribute_values`, `colors`, `warranties`, `notes`, `reviews`, `wishlists` | Products and everything they point at: categories (a tree), brands, attributes and their values, colours, warranty periods, refund/warranty notes, the image gallery, stock per variant, customer reviews and wishlists |
| **Media** (1) | `uploads` | Every uploaded image and PDF. Other tables store an upload's id instead of a file path, so one file can be reused anywhere |
| **Sales** (3) | `orders`, `order_details`, `commission_logs` | Orders, their lines, and the marketplace's commission on each seller's part of an order |
| **People and access** (4) | `users`, `shops`, `roles`, `role_permissions` | One `users` table for admins, staff, sellers and customers (`user_type` says which); a seller's shop; staff roles and the sections each role may open |
| **Support** (6) | `support_tickets`, `support_ticket_replies`, `product_queries`, `product_conversations`, `product_conversation_messages`, `contact_messages` | Tickets with replies, questions asked on product pages, customer–seller conversations and contact-form messages |
| **Reports** (2) | `search_logs`, `wallet_recharges` | What customers search for, and wallet top-ups, for the reports |
| **Framework** (5) | `migrations`, `sessions`, `cache`, `cache_locks`, `personal_access_tokens` | Laravel's own tables: which migrations ran, sign-in sessions, the cache used for sign-in throttling, and API login tokens |

---

## Repository Architecture

```text
wowmart/
├── app/
│   ├── Enums/                  # Fixed value lists: delivery and payment status, user type, discount type, stock visibility, shipping type
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # One controller per panel section (products, orders, reports, support, staff...); thin: validate, call a service, show a page
│   │   │   └── Api/V1/         # The same sections as JSON for the REST API, plus API sign-in
│   │   ├── Middleware/         # EnsureUserIsAdmin: lets in admins, and staff only into the sections their role allows
│   │   ├── Requests/Admin/     # Form validation rules, shared by the panel and the API
│   │   └── Resources/V1/       # How each model is turned into JSON for the API
│   ├── Models/                 # Eloquent models, one per table, with their relationships
│   │   └── Concerns/           # HasUniqueSlug: makes URL slugs like "arduino-uno-board" unique
│   ├── Providers/              # AppServiceProvider: pagination style, sidebar counts, the whereContains() search helper
│   └── Services/               # Business rules, one service per section (order status changes, delete guards, stock restore...)
├── bootstrap/                  # Laravel start-up: middleware aliases, route files, JSON errors for the API
├── config/
│   ├── permissions.php         # Which routes each staff permission opens
│   └── ...                     # Laravel's standard settings (app, auth, database, sanctum...)
├── database/
│   ├── migrations/             # Table definitions, run in order to build the database
│   ├── seeders/                # Demo data: admin, catalogue, brands, orders, reviews, reports, support, staff
│   ├── factories/              # Test data builders used by the automated tests
│   └── sql/wowmart.sql         # The whole demo database, ready to import in phpMyAdmin
├── docs/
│   ├── database/               # Database diagram (SVG) and its Mermaid source
│   ├── screenshots/            # The screenshots shown in this README
│   ├── postman_collection.json # Ready-to-import Postman collection for the API
│   └── openapi.json            # The same API described in OpenAPI 3.1
├── public/
│   ├── css/admin.css           # The panel's whole design, plain CSS
│   ├── images/brand/           # WowMart logo files (SVG)
│   ├── uploads/                # Demo product photos and brand logos
│   └── index.php               # The web entry point every request goes through
├── resources/views/
│   ├── admin/                  # The screens: one folder per section, plus the shared layout and the dashboard
│   └── components/admin/       # Reusable pieces: form field, media picker, on/off switch, status pill, bar chart, menu item
├── routes/
│   ├── web.php                 # Sign-in, sign-out and the /admin group that loads routes/admin/*
│   ├── admin/                  # One web route file per section
│   ├── api.php                 # The /api/v1 group: token sign-in, then routes/api/v1/*
│   ├── api/v1/                 # One API route file per section
│   └── console.php             # Scheduled task: delete expired API tokens daily
├── scripts/                    # Generators that rebuild docs/openapi.json and the Postman collection from the routes
├── storage/                    # Logs, cache and compiled views (created at runtime, not committed)
├── tests/
│   └── Feature/
│       ├── Admin/              # Tests for every panel screen and its rules
│       └── Api/V1/             # Tests for every API endpoint
├── .env.example                # Example settings: copy to .env (database name, user, password)
├── artisan                     # Laravel's command-line tool (serve, migrate, test...)
├── composer.json               # PHP packages the project needs
└── phpunit.xml                 # Test settings (in-memory SQLite database)
```

---

## Screenshots



| | |
|---|---|
| **Sign in** — for admins and staff, with one generic error for every failed attempt<br>![Sign in](docs/screenshots/01-sign-in.png) | **Dashboard** — a to-do list of what needs attention, totals and a 14-day sales chart<br>![Dashboard](docs/screenshots/02-dashboard.png) |
| **All products** — search, filters, and switches for published / featured / today's deal<br>![Products](docs/screenshots/03-products.png) | **Product form** — one long form in seven sections, so every error shows at once<br>![Product form](docs/screenshots/04-product-form.png) |
| **Categories** — a tree with levels, order and product counts<br>![Categories](docs/screenshots/05-categories.png) | **Brands** — list and add on one page<br>![Brands](docs/screenshots/06-brands.png) |
| **All orders** — filter by delivery status, payment status, date or customer<br>![Orders](docs/screenshots/07-orders.png) | **Order details** — lines, totals and the next allowed status steps<br>![Order details](docs/screenshots/08-order-detail.png) |
| **Stock report** — stock value, sold-out and low-stock products, CSV download<br>![Stock report](docs/screenshots/09-stock-report.png) | **Product reviews** — hide or show a review; the product rating updates itself<br>![Reviews](docs/screenshots/10-product-reviews.png) |
| **Support tickets** — customer tickets with replies and status<br>![Support](docs/screenshots/11-support-tickets.png) | **Staff** — staff accounts with a role each<br>![Staff](docs/screenshots/12-staff.png) |
| **Staff permissions** — roles decide which sections a staff member can open<br>![Permissions](docs/screenshots/13-staff-permissions.png) | **Uploaded files** — one media library; products and brands pick images from it<br>![Uploaded files](docs/screenshots/14-uploaded-files.png) |

---

