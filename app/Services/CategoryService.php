<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Every write to a category, for the panel and the API alike.
 *
 * Input is validated already (CategoryRequest), including the rule that a
 * category can never be moved under itself or one of its own descendants.
 */
class CategoryService
{
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $category = new Category($data);
            $category->forceFill([
                'slug' => Category::uniqueSlug($data['name']),
                'level' => $this->levelUnder($data['parent_id'] ?? null),
            ])->save();

            return $category;
        });
    }

    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            // The slug is kept on rename so links already shared keep working.
            $category->fill($data);
            $moved = $category->isDirty('parent_id');
            $category->forceFill(['level' => $this->levelUnder($category->parent_id)])->save();

            if ($moved) {
                // Levels are stored, so a moved branch has to be re-levelled all the way down.
                foreach ($category->generations() as $depth => $ids) {
                    Category::whereKey($ids)->update(['level' => $category->level + $depth + 1]);
                }
            }

            return $category;
        });
    }

    /** Sets exactly the given on/off flags, so repeating a request changes nothing. */
    public function setFlags(Category $category, array $flags): Category
    {
        return DB::transaction(fn () => tap($category)->update($flags));
    }

    /**
     * Subcategories would be orphaned to the top level (parent_id is nullOnDelete)
     * and product links would silently vanish (cascade / nullOnDelete), so both are refused.
     *
     * @throws DomainException when the category still has subcategories or products
     */
    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $children = Category::where('parent_id', $category->id)->count();

            if ($children > 0) {
                throw new DomainException(sprintf(
                    '"%s" cannot be deleted yet. Move or delete its %d %s first.',
                    $category->name, $children, Str::plural('subcategory', $children),
                ));
            }

            // The frequently-bought link is nullOnDelete too, which would leave those
            // products set to "category" with no category behind it.
            $products = Product::where('category_id', $category->id)
                ->orWhere('fq_bought_product_category_id', $category->id)
                ->orWhereIn('id', DB::table('product_categories')->where('category_id', $category->id)->select('product_id'))
                ->count();

            if ($products > 0) {
                throw new DomainException(sprintf(
                    '"%s" is used by %d %s. Move %s to another category first.',
                    $category->name, $products, Str::plural('product', $products), $products === 1 ? 'it' : 'them',
                ));
            }

            $category->delete();
        });
    }

    private function levelUnder(?int $parentId): int
    {
        return $parentId ? Category::whereKey($parentId)->value('level') + 1 : 0;
    }
}
