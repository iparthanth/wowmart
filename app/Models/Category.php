<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * `level` and `slug` are derived from the tree and the name, never submitted.
 */
#[Fillable(['parent_id', 'name', 'order_level', 'banner', 'icon', 'cover_image', 'featured', 'digital', 'meta_title', 'meta_description'])]
class Category extends Model
{
    use HasUniqueSlug;

    protected function casts(): array
    {
        // Ints are cast so a freshly saved model (still holding form strings) serialises like a loaded one.
        return [
            'parent_id' => 'integer',
            'level' => 'integer',
            'order_level' => 'integer',
            'featured' => 'boolean',
            'digital' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order_level');
    }

    /** Every product listed in this category, via the product_categories pivot. */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function bannerFile(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'banner');
    }

    public function iconFile(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'icon');
    }

    public function coverFile(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'cover_image');
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('order_level');
    }

    /**
     * Name contains the term literally: % and _ are escaped so they do not act as wildcards.
     * '!' is the escape character because a backslash is read differently by MySQL and SQLite.
     */
    public function scopeNameContains(Builder $query, string $term): Builder
    {
        return $query->whereRaw("name like ? escape '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%']);
    }

    /**
     * Descendant ids grouped by generation: [children, grandchildren, ...].
     * One query per generation. Ids already seen are dropped, so a cycle
     * that is already in the data ends the walk instead of looping forever.
     *
     * @return list<list<int>>
     */
    public function generations(): array
    {
        $seen = [$this->id];
        $out = [];

        for ($frontier = [$this->id]; $frontier !== [];) {
            $frontier = array_values(array_diff(static::whereIn('parent_id', $frontier)->pluck('id')->all(), $seen));
            if ($frontier !== []) {
                $out[] = $frontier;
                $seen = [...$seen, ...$frontier];
            }
        }

        return $out;
    }

    /** @return list<int> every category below this one, at any depth */
    public function descendantIds(): array
    {
        return array_merge(...$this->generations());
    }

    /**
     * Every category, each parent immediately followed by its children, for
     * pickers and the category list. Indent with `level`.
     */
    public static function inTreeOrder(): Collection
    {
        $byParent = static::orderBy('order_level')->orderBy('name')->get()->groupBy(fn ($c) => (int) $c->parent_id);

        $walk = function (int $parent) use (&$walk, $byParent): Collection {
            return ($byParent[$parent] ?? collect())->flatMap(fn ($c) => [$c, ...$walk($c->id)]);
        };

        return $walk(0)->values();
    }
}
