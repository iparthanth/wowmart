<?php

namespace App\Services;

use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Every write to a product goes through here - the admin screens today, the
 * API tomorrow - so the rules below exist exactly once.
 *
 * Input is expected to be validated already (ProductRequest). Each public
 * method runs in a transaction: a product is never left half-saved with its
 * categories written but its gallery missing.
 */
class ProductService
{
    /** Validated keys that are relations rather than product columns. */
    private const RELATION_KEYS = ['category_ids', 'photos', 'flash_deal_id', 'flash_discount', 'flash_discount_type'];

    public function create(array $data, User $by): Product
    {
        return DB::transaction(function () use ($data, $by) {
            $product = new Product($this->columns($data));

            // Ownership comes from who is signed in, never from the request.
            $product->forceFill([
                'slug' => Product::uniqueSlug($data['name']),
                'added_by' => $by->isAdmin() ? 'admin' : 'seller',
                'user_id' => $by->id,
            ])->save();

            $this->syncRelations($product, $data);

            return $product;
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // The slug survives a rename on purpose: changing it would break every
            // link, bookmark and search result already pointing at the product.
            $product->update($this->columns($data));
            $this->syncRelations($product, $data);

            return $product;
        });
    }

    /** A copy starts unpublished, so it cannot appear in the shop before someone has checked it. */
    public function duplicate(Product $source): Product
    {
        return DB::transaction(function () use ($source) {
            $copy = $source->replicate(['slug', 'num_of_sale', 'rating']);
            $copy->forceFill(['slug' => Product::uniqueSlug($source->name), 'published' => false])->save();

            $copy->categories()->sync($source->categories()->pluck('categories.id'));
            $copy->gallery()->sync(
                $source->gallery->mapWithKeys(fn ($u) => [$u->id => ['sort_order' => $u->pivot->sort_order]])
            );

            foreach ($source->stocks()->with('wholesalePrices')->get() as $stock) {
                $copy->stocks()
                    ->create($stock->only(['variant', 'sku', 'price', 'qty', 'image']))
                    ->wholesalePrices()
                    ->createMany($stock->wholesalePrices->map->only(['min_qty', 'max_qty', 'price'])->all());
            }

            return $copy;
        });
    }

    /**
     * order_details.product_id is nullOnDelete, so deleting a product that has
     * been sold would quietly strip its name from past orders. Those products
     * are unpublished instead; only never-sold products can be deleted.
     *
     * @throws DomainException when the product appears on any order
     */
    public function delete(Product $product): void
    {
        $lines = OrderDetail::where('product_id', $product->id)->count();

        if ($lines > 0) {
            throw new DomainException(sprintf(
                '"%s" is on %d order %s, so it cannot be deleted. Unpublish it instead.',
                $product->name, $lines, Str::plural('line', $lines),
            ));
        }

        $product->delete(); // gallery, categories, stock rows and flash-deal entries cascade
    }

    private function columns(array $data): array
    {
        $columns = Arr::except($data, self::RELATION_KEYS);

        // No thumbnail picked but gallery images given: use the first one rather
        // than letting the product fall into the "missing images" queue.
        $columns['thumbnail_img'] ??= $data['photos'][0] ?? null;

        return $columns;
    }

    /**
     * The form always posts its full state, so an absent list means "none":
     * unticking every gallery image really does empty the gallery.
     */
    private function syncRelations(Product $product, array $data): void
    {
        // The main category is always one of the listed categories.
        $product->categories()->sync(
            collect($data['category_ids'] ?? [])->push($data['category_id'])->map(fn ($id) => (int) $id)->unique()->values()
        );

        $product->gallery()->sync(
            collect($data['photos'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()
                ->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i]])
        );

        // Simple products keep exactly one stock row, so stock is always read from one place.
        $product->stocks()->updateOrCreate(['variant' => ''], [
            'sku' => $product->sku,
            'price' => $product->unit_price,
            'qty' => $product->current_stock,
        ]);

        $product->flashDeals()->sync(empty($data['flash_deal_id']) ? [] : [
            $data['flash_deal_id'] => [
                'discount' => $data['flash_discount'],
                'discount_type' => $data['flash_discount_type'],
            ],
        ]);
    }
}
