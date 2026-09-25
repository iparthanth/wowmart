<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A price rule AND a landing page of its own (slug, banner, featured).
 */
#[Fillable(['title', 'banner', 'start_date', 'end_date', 'background_color', 'text_color'])]
class FlashDeal extends Model
{
    use HasUniqueSlug;

    /** state() value => [label, pill class]. */
    public const STATES = [
        'running' => ['Running', 'pill-ok'],
        'scheduled' => ['Scheduled', 'pill-info'],
        'ended' => ['Ended', 'pill-mute'],
        'off' => ['Off', 'pill-mute'],
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_deal_products')
            ->withPivot('discount', 'discount_type');
    }

    public function bannerFile(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'banner');
    }

    public function isRunning(): bool
    {
        return $this->status && $this->start_date->isPast() && $this->end_date->isFuture();
    }

    public function state(): string
    {
        return match (true) {
            ! $this->status => 'off',
            $this->start_date?->isFuture() ?? false => 'scheduled',
            $this->end_date?->isPast() ?? false => 'ended',
            default => 'running',
        };
    }

    /**
     * Why this discount cannot go on a product at this price, or null when it can.
     * Every form that writes the flash_deal_products pivot should ask this, so the caps live in one place.
     */
    public static function discountProblem(float $discount, ?string $type, float $price): ?string
    {
        return match (true) {
            $type === DiscountType::Percent->value && $discount > 100 => 'A percentage discount cannot be more than 100.',
            // A flat discount above the price would sell the product below zero.
            $type === DiscountType::Amount->value && $discount > $price => sprintf('A flat discount cannot be more than the price (৳%s). Lower it or use a percentage.', number_format($price, 2)),
            default => null,
        };
    }

    /** The deal's landing page in the storefront app. */
    public function pageUrl(): string
    {
        return config('services.storefront.url').'/flash-deal/'.$this->slug;
    }
}
