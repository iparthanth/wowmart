# Admin screens — captured contracts

Read-only capture from the live panel, 18 Sep 2026 (updated 26 Sep 2026). No
writes, no button clicks, no customer records read. Order and customer rows were
skipped deliberately; only column headers and filter contracts were taken from
those screens.

## Sidebar structure (26 Sep 2026)

Re-captured from the live DOM. The reference sidebar is a **MetisMenu
accordion**: 17 top-level collapsible groups, nested sub-groups up to 4 levels
(Setup & Configurations → Social Media Logins → Google → Analytics Tools).
Groups auto-open only along the active route; everything else stays collapsed.
Group order:

Dashboard · POS System · Products · Sales · Customers · Sellers · Reports ·
Blog System · Marketing · Support · Affiliate System · OTP System · Payment
Gateways · Website Setup · Setup & Configurations · Staffs · System

Sub-structure worth noting:

- **Products** is a wide hub: Add New / All / In House / Seller (Physical,
  Digital) / Bulk Import / Bulk Export / Category / Category Based Discount /
  Brand (All, Bulk Import) / Attribute / Colors / Size Guide (Size Charts,
  Measurement Points) / Warranty / Product Reviews / Notes (Add, List) /
  Wholesale Products (Add, All, In House, Seller)
- **Sales** mixes orders and delivery ops: All / Inhouse / Seller / Pickup Point
  / Unpaid orders, then Delivery Boy (All, Add, Payment Histories, Collected
  Histories, Cancel Request, Configuration) and Refunds (Requests, Approved,
  Rejected, Configuration)
- **Sellers** holds seller commerce: All, Rating & Followers, Payouts, Payout
  Requests, Seller Commission, Seller Based Commission, Category Based
  Commission, Seller Packages, Verification Form, Uploaded Files
- **Reports**: Earning Report, In House Product Sale, Seller Products Sale,
  Products Stock, Products Wishlist, User Searches, Commission History, Wallet
  Recharge History
- **Setup & Configurations**: Features Activation, Languages, Currency, Vat &
  Tax, Pickup Point, SMTP Settings, Order Configuration, File System & Cache
  Configuration, Social Media Logins (Facebook → WhatsApp Chat, Facebook
  Comment; Google → Analytics, reCAPTCHA, Map, Firebase), Shipping
  (Configuration, Countries, States, Cities, Zones, Carriers)
- Entry hrefs use top-level paths (`/admin/brands`, `/admin/wish_report`), not
  group-prefixed paths

WowMart mirrors this 1:1 in
`resources/views/admin/layouts/app.blade.php` (the `$menu` tree) rendered by
`resources/views/components/admin/nav-node.blade.php` (`<details>` accordion,
same auto-open rule). Routes that exist link; the rest render as muted
"not built yet" placeholders so the structure matches without dead links.

## Product Wish Report `/admin/wish_report` (26 Sep 2026)

| Element | Contract |
|---|---|
| Card title | "Product Wish Report" |
| Filters | two `Choose Category` selects + Filter button — first select filters rows, second appears redundant |
| Columns | Product Name · Number of Wish **(no image column)** |
| Rows | **every product**, products with zero wishes included |
| Order | wishlist count desc, then product id |
| Empty state | none observed — the list never empties |

WowMart matches this (all products, zeros included, thumbnails added) since
26 Sep 2026; our category filter is a single tree-ordered select and the report
also batch-resolves thumbnails, unlike the reference.

## Panel shape

- Sign-in `/login` → panel `/admin`, unlinked from any public page
- **23 top-level sections, ~140 sub-pages** — full tree in `admin_menu.json`
- Top bar: Dashboard · Orders · Earnings · Homepage Settings · **Add New +**
  (quick-create: New Product / New Category / New Brand)

## Dashboard `/admin`

Six count tiles — Total Customer, Total Products, Total Sales, Total Sellers,
Total Category, Total Brands — plus Inhouse vs Sellers product split, monthly
sales figure, a yearly sales line chart, pending/approved seller counts, and
Top Brands / Top Category / In-house Top Category / In-house Top Brands lists.

## All Products `/admin/products/all`

| Column | Notes |
|---|---|
| checkbox | bulk select, `id[]` |
| Name | thumbnail + title |
| Added By | owning account |
| Info | Num of Sale · Base Price · Rating, stacked |
| Total Stock | |
| Todays Deal | **inline toggle** → `update_todays_deal()` |
| Published | **inline toggle** → `update_published()` |
| Featured | **inline toggle** → `update_featured()` |
| Options | view · edit · duplicate · delete |

Filters: `user_id` (seller) · `type` (sort) · `search`
Sort: rating, num_of_sale, unit_price — each asc/desc
Row links: `/admin/products/admin/{id}/edit?lang=en` · `/admin/products/duplicate/{id}?type=All`

Those three toggles write immediately over AJAX — no confirm step. Worth
mirroring the convenience, worth adding the confirm they omitted.

## Categories `/admin/categories`

Columns: `#` · Name · Parent Category · Order Level · Level · Banner · Icon ·
Cover Image · Featured · Options. Buttons: Add New category · Delete.

Three separate images per category (banner, icon, cover) — our schema has
`icon_id` and `banner_id` but no cover.

## Uploaded Files `/admin/uploaded-files`

Grid of cards: thumbnail, filename, file size, select checkbox, per-card menu.
Toolbar: Bulk Action · `sort` (`newest|oldest|smallest|largest`) · `search` ·
Upload New File. Bulk select via `id[]`.

## All Orders `/admin/all_orders`

Columns: checkbox · Order Code · Num. of Products · Customer · Seller · Amount ·
Delivery Status · Payment method · Payment Status · Refund · Options

Filters: `delivery_status` · `payment_status` · `date` · `search` · `order_ids`

### Delivery status — authoritative list

```
pending · confirmed · picked_up · on_the_way · delivered · cancelled
```

**Six states, not seven.** Our storefront schema invented a `processing` state
that the reference does not have. Align to these six before building the order
screens, or the admin and storefront will disagree about what an order is.

Payment status: `paid` · `unpaid`.

## Deltas against our schema

| Reference has | We have |
|---|---|
| 6 delivery statuses | 7 (`processing` is ours, and wrong) |
| category cover image | banner + icon only |
| product `barcode`, `quick_overview`, `tags[]` | neither |
| `low_stock_quantity`, `stock_visibility_state` | neither |
| colors / attributes → **variants** | none — largest gap |
| per-product shipping, warranty, refundable | none |
| frequently-bought-together | none |

## Order detail `/admin/orders/{encrypted-id}`

Opened once, with the owner's explicit permission, purely to capture the control
structure. No customer field was read or recorded.

### URL design — worth copying

The order id in the path is **Laravel `encrypt()` output** — the base64 of a
`{"iv":…,"value":…,"mac":…,"tag":""}` payload, not a sequential integer:

```
/admin/orders/eyJpdiI6IjlUVm41WEVXVi9jNEhpWDB6VEdoRWc9PSIs…
```

So order URLs cannot be enumerated by incrementing a number, and a leaked URL
reveals no id. Mirror this: bind the route with a decrypting resolver rather
than exposing `orders.id`.

### Page structure

| Region | Shape |
|---|---|
| Summary table | 6 label/value rows — order meta |
| Line items | `#` · Photo · Description · **Delivery Type** · QTY · Price · Total |
| Totals table | 5 rows — subtotal → grand total |

`Delivery Type` sits on the **line item**, not the order. In a multi-vendor
marketplace each seller's lines can ship differently, so shipping is per-line.
Our `order_details` table has no such column.

### What is NOT on this page

**No delivery-status or payment-status control.** The page carries no `<form>`
and no `<select>` at all — it is a read-only view in this deployment. Status
transitions therefore happen elsewhere (the list's Options column, or a role
this account does not hold).

Consequence for the build: do not assume the status dropdown lives on the order
detail page. The six authoritative statuses come from the list filter, which is
captured above; where the admin *changes* them is still unconfirmed.

---

## Brands `/admin/brands`

List and create on **one page** — no separate create screen.

- Columns: `#` · Name · Logo · Options
- Search: `GET ?search=`
- Inline create: `POST /admin/brands` — `name`, `logo` (upload id), `meta_title`, `meta_description`

Brands carry their own SEO fields, like products and categories do.

## Coupon `/admin/coupon`

- Columns: `#` · Code · Type · Start Date · End Date · Validation Days · Status · Options
- Create: `/admin/coupon/create` → `POST /admin/coupon`

The create page renders **only** a `type` selector up front:

| `type` | Meaning |
|---|---|
| `product_base` | discount applies to chosen products |
| `cart_base` | discount on the cart total, with minimum spend |
| `welcome_base` | first-order / new-customer coupon |

Choosing a type calls `coupon_form()` → `POST /admin/coupon/get_form`, which
returns that type's field set. **That request was deliberately not fired** — it is
a POST, and the capture rule was no operations of any kind. The per-type fields
are therefore *inferred*, not observed.

Our schema supports only the cart-based kind. Three coupon types is a real gap.

## Flash deals `/admin/flash_deals`

- Columns: `#` · Title · Banner · Start Date · End Date · Status · Featured · Page Link · Options
- Create: `/admin/flash_deals/create` → `POST /admin/flash_deals`
  - `title`, `banner` (upload id), `date_range`, `products[]` (multi-select)
- After products are picked, `/admin/flash_deals/product_discount` loads one
  discount row per product. Not fired.

`Featured` and `Page Link` mean a flash deal is also a **landing page** of its own,
not just a price rule. Our `flash_deals` table has neither column.

## Select Homepage `/admin/website/select-homepage`

Captured by reading page source only. No option was clicked.

- `POST /admin/business-settings/update`
- `homepage_select` radio: `classic` · `metro` · `minima` · `megamart` · **`reclassic`** (active)
- `types[]` hidden field

**Correction to earlier notes:** changing the homepage is *select a design, then
press Save* — two actions, not one click. The radios do not auto-submit. It still
changes the live storefront the moment Save is pressed, with no confirm step.

### The settings pattern behind it

This screen posts to a **generic** endpoint, `business-settings/update`, with a
hidden `types[]` naming which setting keys to write. That is the reference
system's configuration model: a key/value `business_settings` table, and many
unrelated settings screens sharing one update route. It explains how dozens of
settings pages across the menu exist without a bespoke controller each.

Worth reproducing — with one improvement: an allow-list of writable keys per
screen, so a crafted `types[]` cannot rewrite a setting its screen never showed.
