<?php

namespace App\Http\Resources\V1;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every ProductRequest field under the same name, so a GET body can be edited
 * and PUT back unchanged (PUT is a full replacement: a missing photos list
 * would clear the gallery). The relations also come back richer for display:
 * gallery [{id,url}], thumbnail, owner.
 *
 * @mixin \App\Models\Product
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $file = fn (?Upload $u) => $u ? ['id' => $u->id, 'url' => $u->url()] : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'added_by' => $this->added_by,
            'owner' => $this->whenLoaded('owner', fn () => $this->owner ? ['id' => $this->owner->id, 'name' => $this->owner->name] : null),
            'published' => $this->published,
            'approved' => $this->approved,
            'num_of_sale' => $this->num_of_sale,
            'rating' => $this->rating,

            // General
            'category_id' => $this->category_id,
            'category_ids' => $this->whenLoaded('categories', fn () => $this->categories->pluck('id')->sort()->values()),
            'brand_id' => $this->brand_id,
            'unit' => $this->unit,
            'weight' => $this->weight,
            'min_qty' => $this->min_qty,
            'tags' => $this->tags,
            'barcode' => $this->barcode,
            'quick_overview' => $this->quick_overview,
            'description' => $this->description,
            'refundable' => $this->refundable,
            'refund_note_id' => $this->refund_note_id,
            'featured' => $this->featured,
            'todays_deal' => $this->todays_deal,

            // Files & media
            'thumbnail_img' => $this->thumbnail_img,
            'thumbnail' => $this->whenLoaded('thumbnail', fn () => $file($this->thumbnail)),
            'photos' => $this->whenLoaded('gallery', fn () => $this->gallery->pluck('id')), // pivot order
            'gallery' => $this->whenLoaded('gallery', fn () => $this->gallery->map($file)->values()),
            'video_provider' => $this->video_provider,
            'video_link' => $this->video_link,
            'pdf' => $this->pdf,

            // Price & stock
            'unit_price' => $this->unit_price,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'discount_start_date' => $this->discount_start_date?->toIso8601String(),
            'discount_end_date' => $this->discount_end_date?->toIso8601String(),
            'current_stock' => $this->current_stock,
            'stock' => $this->whenLoaded('stocks', fn () => $this->stocks->firstWhere('variant', '')?->qty),
            'sku' => $this->sku,
            'low_stock_quantity' => $this->low_stock_quantity,
            'stock_visibility_state' => $this->stock_visibility_state,
            'external_link' => $this->external_link,
            'external_link_btn' => $this->external_link_btn,

            // SEO
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_img' => $this->meta_img,

            // Shipping
            'cash_on_delivery' => $this->cash_on_delivery,
            'shipping_type' => $this->shipping_type,
            'shipping_cost' => $this->shipping_cost,
            'is_quantity_multiplied' => $this->is_quantity_multiplied,
            'est_shipping_days' => $this->est_shipping_days,

            // Warranty
            'has_warranty' => $this->has_warranty,
            'warranty_id' => $this->warranty_id,
            'warranty_note_id' => $this->warranty_note_id,

            // Frequently bought
            'frequently_bought_selection_type' => $this->frequently_bought_selection_type,
            'fq_bought_product_category_id' => $this->fq_bought_product_category_id,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
