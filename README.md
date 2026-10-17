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
They share the password `password`, and each only sees the sections their role opens:

| Staff member | Email | Role | Can open |
|---|---|---|---|
| Sadia Rahman | `sadia@staff.wowmart.test` | Order manager | Sales, reports, support |
| Imran Hossain | `imran@staff.wowmart.test` | Catalogue editor | Products, media |

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

## Screenshots



| | |
|---|---|
| **Sign in** — admin-only, with one generic error for every failed attempt<br>![Sign in](docs/screenshots/01-sign-in.png) | **Dashboard** — a to-do list of what needs attention, totals and a 14-day sales chart<br>![Dashboard](docs/screenshots/02-dashboard.png) |
| **All products** — search, filters, and switches for published / featured / today's deal<br>![Products](docs/screenshots/03-products.png) | **Product form** — one long form in seven sections, so every error shows at once<br>![Product form](docs/screenshots/04-product-form.png) |
| **Categories** — a tree with levels, order and product counts<br>![Categories](docs/screenshots/05-categories.png) | **Brands** — list and add on one page<br>![Brands](docs/screenshots/06-brands.png) |
| **All orders** — filter by delivery status, payment status, date or customer<br>![Orders](docs/screenshots/07-orders.png) | **Order details** — lines, totals and the next allowed status steps<br>![Order details](docs/screenshots/08-order-detail.png) |
| **Stock report** — stock value, sold-out and low-stock products, CSV download<br>![Stock report](docs/screenshots/09-stock-report.png) | **Product reviews** — hide or show a review; the product rating updates itself<br>![Reviews](docs/screenshots/10-product-reviews.png) |
| **Support tickets** — customer tickets with replies and status<br>![Support](docs/screenshots/11-support-tickets.png) | **Staff** — staff accounts with a role each<br>![Staff](docs/screenshots/12-staff.png) |
| **Staff permissions** — roles decide which sections a staff member can open<br>![Permissions](docs/screenshots/13-staff-permissions.png) | **Uploaded files** — one media library; products and brands pick images from it<br>![Uploaded files](docs/screenshots/14-uploaded-files.png) |

---

