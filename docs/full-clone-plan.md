# WowMart Admin — plan for the full panel

Every section of the reference sidebar (`docs/reference/admin_menu.json`, 23 sections,
133 pages) mapped to what WowMart builds: screens, route names, tables and API.
Route names here are a contract: the sidebar links them before they exist and shows
each entry only once its route is registered.

The reference is Active eCommerce CMS, a licensed product. WowMart rebuilds the
*behaviour* it exposes to an admin with original code and its own schema; nothing is
copied from its source.

## Status

| Wave | Sections | State |
|---|---|---|
| 1 | Dashboard, products, categories, brands, orders, media, flash deals, coupons, API v1 | done |
| 2 | Attributes, colours, warranties, notes, reviews, refunds, customers, sellers, 4 reports | done |
| 3 | Staff and permissions, settings framework, sales lists, pickup points, delivery boys, shipping | staff/permissions done; settings next |
| 4 | Website setup, pages, blog, marketing, support | planned |
| 5 | Seller economy, wallet and offline payments, classified, affiliate, OTP/SMS, remaining reports | planned |
| 6 | POS, bulk import/export, digital and wholesale screens, category discount, size guide, languages, system | planned |

Deliberately not built: the reference's **Update** (WowMart deploys with git) and
**Addon Manager** (WowMart has no add-on marketplace), and **"Log in as customer"**
(impersonation from a GET link is a session-hijack risk).

## Cross-cutting rules for every wave

- Web routes `routes/admin/<module>.php`, API `routes/api/v1/<module>.php`; update is PUT only.
- Rules live in services; refusals are `DomainException` (flash on web, 409 on API).
- Delete guards check every foreign key that would silently blank or cascade.
- Search uses `whereContains()`; non-string query params never cause a 500.
- Each module ships feature tests for web and API with exact seeded numbers.
- Migrations are additive and named `2026_MM_DD_<wave><nn>…` so parallel agents never collide.

---

## Wave 3

### Staff and permissions (reference: Staffs → All staffs, Staff permissions)
- Tables: `roles` (name), `role_permissions` (role_id, permission); `users.role_id` (nullable, staff only).
- Permissions are keys in `config/permissions.php`, each mapping to route-name patterns
  (e.g. `orders` → `admin.orders.*`, `admin.refunds.*`). One middleware decides for web
  and API: admins pass; staff pass when their role holds a key matching the route;
  everyone else gets 403. The sidebar hides what the user cannot open.
- Routes: `admin.staff.index|create|store|edit|update|destroy`, `admin.roles.index|create|store|edit|update|destroy`.
- API: `/api/v1/staff`, `/api/v1/roles`, `GET /api/v1/permissions`.
- Guard: a role in use cannot be deleted; the last admin cannot be demoted.

### Settings framework (reference: Setup & Configurations, Payment Gateways, Facebook, Google)
- One registry `config/settings.php`: screen → keys, each with type and validation.
  The reference posts a free `types[]` list to one endpoint; WowMart saves only the keys
  the screen declares, so a crafted form cannot rewrite unrelated settings.
- Secrets (SMTP password, gateway secrets, OAuth secrets, S3 keys) are encrypted at rest
  and never rendered back ("saved — leave blank to keep").
- Screens (route `admin.settings.show|update` with `{screen}`): general, features,
  orders, smtp, payment-methods, social-login, google, facebook, whatsapp, file-system.
  SMTP gets a "send test email" action.
- Currency: `currencies` (name, symbol, code, exchange_rate, status) — `admin.currencies.*`.
- VAT & tax: `taxes` (name, status), `product_taxes` (product_id, tax_id, tax, tax_type) — `admin.taxes.*`.
- API: `GET|PUT /api/v1/settings/{screen}` (secrets masked), `/api/v1/currencies`, `/api/v1/taxes`.

### Sales lists (reference: In-house, Seller, Pickup point, Unpaid orders)
- Views over the existing order list with fixed filters:
  `admin.orders.in-house`, `admin.orders.seller`, `admin.orders.pickup`, `admin.orders.unpaid`.

### Pickup points
- Table `pickup_points` (name, address, phone, staff_id, status); `orders.pickup_point_id`.
- Routes `admin.pickup-points.*`; API `/api/v1/pickup-points`.

### Delivery boys (reference: Delivery Boy menu)
- Delivery boys are users with `user_type = delivery_boy`; `delivery_boy_profiles`
  (user_id, total_collection, total_earning); `orders.delivery_boy_id`,
  `orders.cancel_requested_at`; `delivery_boy_payments` (user_id, amount, note);
  `delivery_boy_collections` (user_id, order_id, amount).
- Assign a delivery boy from the order page; cash collected on a delivered COD order
  is recorded against them; cancel requests are approved or declined by the admin.
- Routes `admin.delivery-boys.*`, `admin.delivery-boys.payments`, `admin.delivery-boys.collections`,
  `admin.delivery-boys.cancel-requests`; settings screen `delivery-boy`.

### Shipping (reference: Shipping menu)
- `countries` (name, code, status), `states` (country_id, name, status),
  `cities` (state_id, name, cost, status), `zones` (name, status) + `zone_countries`,
  `carriers` (name, logo, transit_time, free_shipping, status),
  `carrier_ranges` (carrier_id, billing_type weight|price, min, max),
  `carrier_range_prices` (carrier_range_id, zone_id, price).
- Settings screen `shipping`: method (product-wise, flat, seller-wise, area-wise, carrier) and flat cost.
- Routes `admin.countries.*`, `admin.states.*`, `admin.cities.*`, `admin.zones.*`, `admin.carriers.*`.

## Wave 4

### Website setup
- Settings screens: `homepage` (sliders, banners, featured sections as upload ids and links),
  `header`, `footer`, `appearance` (colours, logo, favicon, meta), `authentication` (layout, image),
  `homepage-select` (the reference's five layouts, as a choice the storefront reads).
- `pages` (type, title, slug, content, meta) — `admin.pages.*`.

### Blog
- `blog_categories` (name, slug), `blogs` (category_id, title, slug, short_description,
  description, banner, meta fields, status) — `admin.blog.*`, `admin.blog-categories.*`.

### Marketing
- `dynamic_popups`, `custom_alerts`, `email_templates` (receiver admin|seller|customer|all,
  identifier, subject, body, status), `subscribers`, `newsletters` (sent log),
  `notification_types`, custom notifications through Laravel's `notifications` table.
- Newsletters and custom notifications are queued jobs; locally the mail driver is `log`.

### Support
- `tickets` + `ticket_replies`, `conversations` + `messages`, `product_queries`, `contacts`.
- Screens reply, close and reopen; unread counts feed the sidebar.

## Wave 5

### Seller economy
- Commission: global setting, `shops.commission_rate` (seller-based), `categories.commission_rate`
  (category-based); `commission_histories` written when an order line is delivered.
- `seller_payouts` (seller_id, amount, method, reference), `seller_withdraw_requests`
  (seller_id, amount, message, status); `shops.balance`.
- `seller_packages` (name, price, product_limit, duration_days, logo); `shops.seller_package_id`, `shops.package_ends_at`.
- Verification form builder (setting JSON) and `shops.verification_info`.
- Seller rating & followers: `shop_followers` (user_id, shop_id).

### Wallet and offline payments
- `wallet_transactions` (user_id, amount, method, details, approved, offline);
  `users.balance`; `manual_payment_methods` (type, heading, description, bank_info, photo);
  offline order and package payments approved from their lists.

### Classified products
- `customer_packages`, `customer_products` (customer listings with approval), `users.customer_package_id`, `users.remaining_uploads`.

### Affiliate
- `affiliate_users`, `affiliate_options`, `affiliate_logs`, `affiliate_withdraw_requests`,
  registration form config; `users.referral_code`, `users.referred_by`.

### OTP and SMS
- Settings screens `otp` and `otp-login`; `sms_templates` (identifier, body, template_id, status);
  bulk SMS through a driver interface (a `log` driver until a gateway is chosen).

### Remaining reports
- Earning report, commission history, wallet recharge history, user searches (`searches` table the storefront fills).

## Wave 6

- POS: product search, cart, walk-in or existing customer, cash/card payment; places orders
  through the same stock-locking rules as the storefront. POS settings screen.
- Bulk import (CSV with per-row validation report) and export for products; brand import.
- Digital products (download file on the product), wholesale products (tier editor).
- Category-based discount (apply a discount window to every product in a category).
- Size guide: `size_charts`, `measurement_points`.
- Languages: `languages` (name, code, rtl, status) + `translations` (lang, key, value) editor.
- System: server status (read-only), sitemap generator for the storefront.
