# Add New Product — reference contract

Captured read-only from the live panel, 18 Sep 2026. No data was written.

```
POST /admin/products/store      enctype: multipart/form-data
```

Seven tab panes, one form, one submit — **not a wizard**. The tabs are visual
grouping over a single POST, which is why validation failure can repopulate
everything at once.

| Pane id | Tab label | Fields |
|---|---|---|
| `general` | General | 19 |
| `files_and_media` | Files & Media | 5 |
| `price_and_stocks` | Price & Stock | 13 |
| `seo` | SEO | 3 |
| `shipping` | Shipping | 5 |
| `warranty` | Warranty | 3 |
| `frequenty-bought-product` | Frequently Bought | 2 |

> The pane id `frequenty-bought-product` is misspelled in the reference source.
> Reproduced here as observed; our implementation spells it correctly.

## general

| Field | Type |
|---|---|
| `name` | text |
| `brand_id` | select |
| `unit` | text |
| `weight` | number |
| `min_qty` | number |
| `tags[]` | text (tokenised) |
| `barcode` | text |
| `category_ids[]` | checkbox — multi-category |
| `category_id` | radio — the *main* category among those ticked |
| `quick_overview` | textarea |
| `description` | textarea (rich text) |
| `files` | file |
| `refundable` | checkbox |
| `refund_note_id` | hidden |
| `featured` | checkbox |
| `todays_deal` | checkbox |
| `flash_deal_id` | select |
| `flash_discount` | number |
| `flash_discount_type` | select |

Note `category_ids[]` + `category_id` together: a product belongs to many
categories but exactly one is primary. Our `category_product` pivot already
carries `is_primary` for this.

## files_and_media

| Field | Type |
|---|---|
| `photos` | hidden — CSV of upload ids from the media picker |
| `thumbnail_img` | hidden — single upload id |
| `video_provider` | select |
| `video_link` | text |
| `pdf` | hidden — upload id |

Images are **never** posted as files here. The picker writes upload ids into
hidden inputs; the upload itself happens on the Uploaded Files screen. This is
the reuse model the mentor demonstrated.

## price_and_stocks

| Field | Type |
|---|---|
| `unit_price` | number |
| `discount` | number |
| `discount_type` | select — `amount` \| `percent` |
| `date_range` | text — discount window |
| `current_stock` | number |
| `sku` | text |
| `low_stock_quantity` | number |
| `stock_visibility_state` | radio — `quantity` \| `text` \| `hide` |
| `colors[]`, `colors_active` | select / checkbox |
| `choice_attributes[]` | select — drives variant generation |
| `external_link`, `external_link_btn` | text |

## seo

`meta_title` (text) · `meta_description` (textarea) · `meta_img` (hidden upload id)

## shipping

`cash_on_delivery` (checkbox) · `shipping_type` (radio) · `flat_shipping_cost`
(number) · `is_quantity_multiplied` (checkbox) · `est_shipping_days` (number)

## warranty

`has_warranty` (checkbox) · `warranty_id` (select) · `warranty_note_id` (hidden)

## frequenty-bought-product

`frequently_bought_selection_type` (radio) · `fq_bought_product_category_id` (select)

## Gap against our schema

Present in the reference, absent from ours — decide in/out before building:

- `barcode` (distinct from `sku`), `quick_overview`, `tags[]`
- `video_provider` / `video_link`, `pdf`
- `low_stock_quantity`, `stock_visibility_state`
- `refundable` / `refund_note_id`, warranty, shipping-per-product
- colors / `choice_attributes[]` → **variants**, the largest single gap
- frequently-bought-together
