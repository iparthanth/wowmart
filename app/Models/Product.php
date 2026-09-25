<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\ShippingType;
use App\Enums\StockVisibility;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fillable is exactly the captured seven-tab form (docs/reference/product-form.md).
 * 
 * Deliberately NOT fillable: slug (derived from the name), added_by and user_id
 * (ownership is decided by who is signed in), published and approved (changed by
 * their own admin actions), and num_of_sale / rating (maintained by the system).
 * None of those may ever come from request input.
 */
#[Fillable(['name', 'category_id', 'brand_id', 'unit', 'weight', 'min_qty', 'tags', 'barcode', 'quick_overview', 'description', 'refundable', 'refund_note_id', 'featured', 'todays_deal', 'thumbnail_img', 'video_provider', 'video_link', 'pdf', 'unit_price', 'discount', 'discount_type', 'discount_start_date', 'discount_end_date', 'current_stock', 'sku', 'low_stock_quantity', 'stock_visibility_state', 'variant_product', 'colors', 'choice_attributes', 'choice_options', 'external_link', 'external_link_btn', 'meta_title', 'meta_description', 'meta_img', 'cash_on_delivery', 'shipping_type', 'shipping_cost', 'is_quantity_multiplied', 'est_shipping_days', 'has_warranty', 'warranty_id', 'warranty_note_id', 'frequently_bought_selection_type', 'fq_bought_product_category_id'])]
class Product extends Model
{
    use HasUniqueSlug;

    /** Admin list sort key => [column, direction]. Anything else falls back to newest. */
    public const ADMIN_SORTS = [
        'newest' => ['id', 'desc'],
        'price_asc' => ['unit_price', 'asc'],
        'price_desc' => ['unit_price', 'desc'],
        'best_selling' => ['num_of_sale', 'desc'],
        'stock_asc' => ['current_stock', 'asc'],
    ];

    /**
     * The admin list filters (panel and API) from raw query input.
     *
     * Unknown values are ignored rather than rejected: a hand-edited or stale
     * bookmarked URL should still open the list.
     */
    public static function adminFilters(array $in): array
    {
        $pick = fn (string $key, array $allowed) => in_array($in[$key] ?? null, $allowed, true) ? $in[$key] : null;

        return [
            'search' => is_string($in['search'] ?? null) ? trim($in['search']) : '',
            'added_by' => $pick('added_by', ['admin', 'seller']),
            'category' => is_numeric($in['category'] ?? null) && (int) $in['category'] > 0 ? (int) $in['category'] : null,
            'stock' => $pick('stock', ['out', 'low']),
            'image' => $pick('image', ['missing']),
            'published' => $pick('published', ['1', '0']),
            'sort' => $pick('sort', array_keys(self::ADMIN_SORTS)) ?? 'newest',
        ];
    }

    /** Applies adminFilters() output: filtering plus a stable sort. */
    public function scopeAdminFilter(Builder $query, array $f): Builder
    {
        [$column, $direction] = self::ADMIN_SORTS[$f['sort']];

        return $query
            ->when($f['search'], fn (Builder $q, string $s) => $q->where(
                fn (Builder $q) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")
            ))
            ->when($f['added_by'], fn (Builder $q, string $v) => $q->where('added_by', $v))
            ->when($f['category'], fn (Builder $q, int $id) => $q->whereHas('categories', fn (Builder $c) => $c->whereKey($id)))
            ->when($f['stock'] === 'out', fn (Builder $q) => $q->where('current_stock', '<=', 0))
            ->when($f['stock'] === 'low', fn (Builder $q) => $q->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'low_stock_quantity'))
            ->when($f['image'], fn (Builder $q) => $q->whereNull('thumbnail_img'))
            ->when($f['published'] !== null, fn (Builder $q) => $q->where('published', (bool) $f['published']))
            ->orderBy($column, $direction)
            ->orderByDesc('id'); // stable paging when the sort column ties
    }

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'rating' => 'decimal:2',
            'discount_type' => DiscountType::class,
            'stock_visibility_state' => StockVisibility::class,
            'shipping_type' => ShippingType::class,
            'discount_start_date' => 'datetime',
            'discount_end_date' => 'datetime',
            'colors' => 'array',
            'choice_attributes' => 'array',
            'choice_options' => 'array',
            'refundable' => 'boolean',
            'featured' => 'boolean',
            'todays_deal' => 'boolean',
            'variant_product' => 'boolean',
            'cash_on_delivery' => 'boolean',
            'is_quantity_multiplied' => 'boolean',
            'has_warranty' => 'boolean',
            'published' => 'boolean',
            'approved' => 'boolean',
            'wholesale_product' => 'boolean',
            'digital' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** The single MAIN category - the radio button on the form. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Every category the product is listed in - the checkboxes on the form. */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function gallery(): BelongsToMany
    {
        return $this->belongsToMany(Upload::class, 'product_upload')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'thumbnail_img');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function refundNote(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'refund_note_id');
    }

    public function warrantyNote(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'warranty_note_id');
    }

    public function flashDeals(): BelongsToMany
    {
        return $this->belongsToMany(FlashDeal::class, 'flash_deal_products')
            ->withPivot('discount', 'discount_type');
    }

    /** Visible on the storefront: published by its owner AND approved by an admin. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('published', true)->where('approved', true);
    }

    public function scopeInHouse(Builder $query): Builder
    {
        return $query->where('added_by', 'admin');
    }

    public function scopeFromSellers(Builder $query): Builder
    {
        return $query->where('added_by', 'seller');
    }

    public function hasActiveDiscount(): bool
    {
        if ((float) $this->discount <= 0) {
            return false;
        }

        return ($this->discount_start_date === null || $this->discount_start_date->isPast())
            && ($this->discount_end_date === null || $this->discount_end_date->isFuture());
    }

    public function discountedPrice(): float
    {
        $price = (float) $this->unit_price;

        if (! $this->hasActiveDiscount()) {
            return $price;
        }

        $off = $this->discount_type === DiscountType::Percent
            ? $price * (float) $this->discount / 100
            : (float) $this->discount;

        return max(0.0, round($price - $off, 2));
    }

    public function isLowStock(): bool
    {
        return $this->current_stock > 0 && $this->current_stock <= $this->low_stock_quantity;
    }
}
