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

## Database Architecture

The panel uses one **MySQL** database, `wowmart`, with **34 tables** (InnoDB, `utf8mb4_unicode_ci`).
Every table is defined by a migration in `database/migrations/`, the demo data comes from
`database/seeders/`, and `database/sql/wowmart.sql` is the same database as a ready-to-import file.

### Diagram

**[Open the interactive diagram in Mermaid Live Editor](https://mermaid.live/edit#pako:eNqtWclu4zgQ_RVD5-4fyHkwl740MJhbAIEWaZkdiVS4OONJ8u9d3GRuWhy3T4FYG2t5VcW8Nx3HpHlqiPiLol6g8Zkd4IeUEvSoFWkvaNBEHt7dd_OjTB0oPvz8kX66scDh39GhhO-sP1hB7utnpmRLvJfA0JgKOArE8N3M0Uc56P7wb3aRgfc8ucCR8-Gg-JTo7pAiPRd0l28mJAhTuWOsMnIhwwPGcoGJaDMp5uCIGCOi0Ec7zoqPHb-AEDqinpQXPxGktCA4-4xpTxUaUp_wgYtH4mGSMZM4jlRKylkLYdnjaueQegq6s5uSwCPJMJRMmHTgkuGA8EhZe7OkpPD8BAkGarILMIU61Y5ESnDvI84hI6JlqkxnziJaRf5Th1dNxDX7Jsg0XMucUhBcmVjM-O6CVNcpV42J7ASd1OwnL9W5HhMFd_hyGJdjZYtMcKw7tQg_giJVDd8kaEcqUT3TaQJWCL1UqapXjZiiquLPwJO6xp9iMlCotGsbuz2OJbqOBicqUbH-2BkWk-AFUGh5jz9_SYCJ-TIIYwH5--X7VJ1Ru2zs_t6Ae6v4DDLxYcf1BJiAqYS_mEpcFfLgPoSuJ49FRyfnGp_lqjgDP0ibX3tLPdaa8FdMqAXPlpvXtWnUoy7QUvHxngQKCEHVQPagTlBuoGtfxJYSeuEaARhligH3YWOQDd7oXh7waYxJTJWaX_QmSlkYihHIWpC08MxoPQ0c4ccSwcmopgEXqrUoVVO-E7kAaAhuj9ftSN89KNWqeJ6VLNRUDgQ5aTgxLTE_XpuNFMfoKqHboWwoU2c9Hhm0QBi2-jJz8ak6gGhGVbvQpFIEjLHZH2Tg66pZCIe8kMXryefm4TdH2pZtz5yP0NSr93lDAhyrqi6fz2q-NQSn1_bIdX82N0_w_FqNxKSPA5XnIhRoAvZL_NkIZ3ps-amVaKi4VABiZlOcIBdK3h4E0SXAivXNkGTGTRKH1d6mAkmCD6SdiPDD6R4bLUu9nm6CCiV3jK5z8Xl2CZNxd94zwXsh2QDrSlHqQcl1lwZ9Zz59tYU8vi4GcIdGdqKd6-lLQ04l16SeJoOlinYvRLWmN-1riBnjo2NEKu5Pj57Bo_r4i3RqT-91rWenHScKKQ6zX08ZGtpqMC1JerJWpZZc0v_JyhYULJW7R3WrrUTolQpdXg0Tv8fbYXJgccQ-DuDE6DcEY5xJt-6MRP_lCWxemsdqVwpDP_SMM8elK6EnSFiX3RhcjgcWzLNHB99J6O6lFRI-FQB9A3qH-sNDZ_nI9fHx_Tt_L9_Wng7PTfx69tykGf_BLZ9_7TLUBnMWqKKtx1C6Z6B9tNEz0D4G85gUKKOjOvH8BhY4_ErrvZK_9BiW8AgwWyM3GOZdpK4jfYSoaZjHVX-HkuMW6cyqJYbCppJ-nfAjJ_RJWPO8o61swDa-twGqvO8qZ3np-pKZS6muxbHAbPGt3nxbWuaQTW7vjttSu-mOgnkxDbYYiygv6Qyb8B3aYpbcJ2khZ4usLea47peMiugXc6Jg8ovnwkWCYYs8884ZWDwQplfxcOc3uWVUSsgrFbHBsbqWrDvbCQjb0h5aWAgDmXuVrRGlO-oesckWupZQ1UyK-m1NeLT0bdueb4FLmRT2rx21EJNmlrstJtDle5P1pZu7qkgdLzHbBeZWkKJXx5beSDJR-eQd6OuLgQWVfPSvK1wWsIaguTnL9O7qwRPLEfB0MjS0utud9mIurYjN0-U20e1ImJQ4ktx8g2IVMFnj5ql5N2VDYPRunp4bTE4IltHn5hNokFb8nyvrmiclNPkGcIUBEfx_U93Hz99aAlfE)** — zoom, pan and
export it as PNG or SVG. The full-size image is also in the repository:
[`docs/database/wowmart-erd.svg`](docs/database/wowmart-erd.svg) (source:
[`docs/database/wowmart-erd.mmd`](docs/database/wowmart-erd.mmd)).

It shows the 29 business tables with their primary keys (PK), foreign keys (FK) and unique
columns (UK). Each line is a foreign key: `||--o{` means the child row must have a parent,
`|o--o{` means the link is optional. Very wide tables (products, orders, users) show only
the columns that explain the design.

<details>
<summary><b>Show the diagram here</b> (GitHub draws it from the code below)</summary>

```mermaid
erDiagram
    attribute_values {
        int id PK
        int attribute_id FK
        string value
    }
    attributes {
        int id PK
        string name
    }
    brands {
        int id PK
        string name
        string slug UK
        int logo FK
        bool top
    }
    categories {
        int id PK
        int parent_id FK
        int level
        string name
        string slug UK
        int order_level
        int banner FK
        int icon FK
        int cover_image FK
        bool featured
        bool digital
    }
    colors {
        int id PK
        string name
        string code
    }
    commission_logs {
        int id PK
        int order_id FK
        string order_code
        int seller_id FK
        decimal admin_commission
        decimal seller_earning
    }
    contact_messages {
        int id PK
        string name
        string email
        string phone
        text query
        text reply
        string status
    }
    notes {
        int id PK
        string type
        text description
    }
    order_details {
        int id PK
        int order_id FK
        int seller_id FK
        int product_id FK
        string variation
        decimal price
        decimal shipping_cost
        int quantity
        string shipping_type
        string delivery_status
        string payment_status
    }
    orders {
        int id PK
        string code UK
        int user_id FK
        int seller_id FK
        json shipping_address
        string delivery_status
        string payment_type
        string payment_status
        decimal grand_total
        decimal coupon_discount
    }
    product_categories {
        int id PK
        int product_id FK
        int category_id FK
    }
    product_conversation_messages {
        int id PK
        int product_conversation_id FK
        int user_id FK
        text message
    }
    product_conversations {
        int id PK
        int product_id FK
        int customer_id FK
        int seller_id FK
        string title
        string status
    }
    product_queries {
        int id PK
        int user_id FK
        int product_id FK
        text question
        text reply
        string status
    }
    product_stocks {
        int id PK
        int product_id FK
        string variant
        string sku
        decimal price
        int qty
        int image FK
    }
    product_upload {
        int id PK
        int product_id FK
        int upload_id FK
        int sort_order
    }
    products {
        int id PK
        string added_by
        int user_id FK
        string name
        string slug UK
        int category_id FK
        int brand_id FK
        int refund_note_id FK
        bool featured
        bool todays_deal
        int thumbnail_img FK
        int pdf FK
        decimal unit_price
        decimal discount
        string discount_type
        int current_stock
        string sku
        int low_stock_quantity
        int meta_img FK
        int warranty_id FK
        int warranty_note_id FK
        int fq_bought_product_category_id FK
        bool published
        bool approved
        int num_of_sale
        decimal rating
    }
    reviews {
        int id PK
        int product_id FK
        int user_id FK
        int rating
        text comment
        bool status
    }
    role_permissions {
        int id PK
        int role_id FK
        string permission
    }
    roles {
        int id PK
        string name UK
    }
    search_logs {
        int id PK
        string query
        int results
        int user_id FK
    }
    shops {
        int id PK
        int user_id FK
        string name
        string slug UK
        int logo FK
        string verification_status
        decimal rating
    }
    support_ticket_replies {
        int id PK
        int support_ticket_id FK
        int user_id FK
        text message
    }
    support_tickets {
        int id PK
        string code UK
        int user_id FK
        string subject
        string status
    }
    uploads {
        int id PK
        string file_original_name
        string file_name
        int user_id FK
        int file_size
        string type
    }
    users {
        int id PK
        string user_type
        int role_id FK
        string name
        string email UK
        string phone UK
        bool banned
    }
    wallet_recharges {
        int id PK
        int user_id FK
        decimal amount
        string payment_method
        string transaction_id
        string approval
    }
    warranties {
        int id PK
        string text
    }
    wishlists {
        int id PK
        int user_id FK
        int product_id FK
    }
    attributes ||--o{ attribute_values : "attribute_id"
    uploads |o--o{ brands : "logo"
    uploads |o--o{ categories : "banner"
    uploads |o--o{ categories : "cover_image"
    uploads |o--o{ categories : "icon"
    categories |o--o{ categories : "parent_id"
    orders ||--o{ commission_logs : "order_id"
    users ||--o{ commission_logs : "seller_id"
    orders ||--o{ order_details : "order_id"
    products |o--o{ order_details : "product_id"
    users |o--o{ order_details : "seller_id"
    users |o--o{ orders : "seller_id"
    users ||--o{ orders : "user_id"
    categories ||--o{ product_categories : "category_id"
    products ||--o{ product_categories : "product_id"
    product_conversations ||--o{ product_conversation_messages : "product_conversation_id"
    users ||--o{ product_conversation_messages : "user_id"
    users ||--o{ product_conversations : "customer_id"
    products ||--o{ product_conversations : "product_id"
    users ||--o{ product_conversations : "seller_id"
    products ||--o{ product_queries : "product_id"
    users ||--o{ product_queries : "user_id"
    uploads |o--o{ product_stocks : "image"
    products ||--o{ product_stocks : "product_id"
    products ||--o{ product_upload : "product_id"
    uploads ||--o{ product_upload : "upload_id"
    brands |o--o{ products : "brand_id"
    categories |o--o{ products : "category_id"
    categories |o--o{ products : "fq_bought_product_category_id"
    uploads |o--o{ products : "meta_img"
    uploads |o--o{ products : "pdf"
    notes |o--o{ products : "refund_note_id"
    uploads |o--o{ products : "thumbnail_img"
    users ||--o{ products : "user_id"
    warranties |o--o{ products : "warranty_id"
    notes |o--o{ products : "warranty_note_id"
    products ||--o{ reviews : "product_id"
    users ||--o{ reviews : "user_id"
    roles ||--o{ role_permissions : "role_id"
    users |o--o{ search_logs : "user_id"
    uploads |o--o{ shops : "logo"
    users ||--o{ shops : "user_id"
    support_tickets ||--o{ support_ticket_replies : "support_ticket_id"
    users ||--o{ support_ticket_replies : "user_id"
    users ||--o{ support_tickets : "user_id"
    users |o--o{ uploads : "user_id"
    roles |o--o{ users : "role_id"
    users ||--o{ wallet_recharges : "user_id"
    products ||--o{ wishlists : "product_id"
    users ||--o{ wishlists : "user_id"
```

</details>

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

### How the design works

- **One account table.** Admins, staff, sellers and customers are all rows in `users`, told
  apart by `user_type`. Staff also have a `role_id`, and `role_permissions` lists the
  sections that role can open.
- **Main category plus extra categories.** `products.category_id` is the main category.
  `product_categories` lists every category the product appears in, the main one included.
- **Images by reference.** Products, categories, brands and shops store upload ids, for example
  `products.thumbnail_img` and `brands.logo`. A product's gallery is `product_upload`, whose
  `sort_order` keeps the order the images were picked in.
- **Stock in one place.** Every product has at least one `product_stocks` row. A simple product
  has a single row with an empty `variant`, so stock is always read the same way.
- **Orders keep their history.** An order line stores the price, quantity, shipping cost and
  seller at the time of sale, and the order stores the delivery address as a JSON snapshot.
  Editing a product or an address later never changes a past order.
- **Delete rules protect data.** Foreign keys either delete child rows with their parent
  (`CASCADE`, for example order lines, gallery links, ticket replies), clear an optional link
  (`SET NULL`, for example a product's brand, category, images or notes), or refuse the delete
  (`RESTRICT`: a customer who has orders can't be deleted). On top of that, the services refuse
  deletes that would silently lose data, such as a category that still has products or an
  image that is still in use.
- **Statuses are fixed lists.** Delivery status (`pending`, `confirmed`, `picked_up`,
  `on_the_way`, `delivered`, `cancelled`) and payment status (`paid`, `unpaid`) are stored as
  text and checked against PHP enums in `app/Enums/`.

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
| **Sign in** — admin-only, with one generic error for every failed attempt<br>![Sign in](docs/screenshots/01-sign-in.png) | **Dashboard** — a to-do list of what needs attention, totals and a 14-day sales chart<br>![Dashboard](docs/screenshots/02-dashboard.png) |
| **All products** — search, filters, and switches for published / featured / today's deal<br>![Products](docs/screenshots/03-products.png) | **Product form** — one long form in seven sections, so every error shows at once<br>![Product form](docs/screenshots/04-product-form.png) |
| **Categories** — a tree with levels, order and product counts<br>![Categories](docs/screenshots/05-categories.png) | **Brands** — list and add on one page<br>![Brands](docs/screenshots/06-brands.png) |
| **All orders** — filter by delivery status, payment status, date or customer<br>![Orders](docs/screenshots/07-orders.png) | **Order details** — lines, totals and the next allowed status steps<br>![Order details](docs/screenshots/08-order-detail.png) |
| **Stock report** — stock value, sold-out and low-stock products, CSV download<br>![Stock report](docs/screenshots/09-stock-report.png) | **Product reviews** — hide or show a review; the product rating updates itself<br>![Reviews](docs/screenshots/10-product-reviews.png) |
| **Support tickets** — customer tickets with replies and status<br>![Support](docs/screenshots/11-support-tickets.png) | **Staff** — staff accounts with a role each<br>![Staff](docs/screenshots/12-staff.png) |
| **Staff permissions** — roles decide which sections a staff member can open<br>![Permissions](docs/screenshots/13-staff-permissions.png) | **Uploaded files** — one media library; products and brands pick images from it<br>![Uploaded files](docs/screenshots/14-uploaded-files.png) |

---

