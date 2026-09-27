<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * One file in the shared media library. Everything else refers to uploads by id.
 */
#[Fillable(['file_original_name', 'file_name', 'user_id', 'file_size', 'extension', 'type'])]
class Upload extends Model
{
    /**
     * Every column in the schema that points at an upload, as label => [table, column].
     *
     * Those foreign keys are nullOnDelete or cascade, so deleting a file that is
     * still in use would silently blank a thumbnail or logo. UploadTest compares
     * this list against the real schema, so a new column cannot be missed.
     */
    public const REFERENCES = [
        'product thumbnail' => ['products', 'thumbnail_img'],
        'product gallery image' => ['product_upload', 'upload_id'],
        'product share image' => ['products', 'meta_img'],
        'product PDF' => ['products', 'pdf'],
        'variant image' => ['product_stocks', 'image'],
        'brand logo' => ['brands', 'logo'],
        'category banner' => ['categories', 'banner'],
        'category icon' => ['categories', 'icon'],
        'category cover' => ['categories', 'cover_image'],
        'flash deal banner' => ['flash_deals', 'banner'],
        'blog post banner' => ['blogs', 'banner'],
        'pop-up banner' => ['dynamic_popups', 'banner'],
        'shop logo' => ['shops', 'logo'],
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        return asset($this->file_name);
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->file_size;

        return $bytes >= 1048576
            ? round($bytes / 1048576, 1).' MB'
            : round($bytes / 1024, 1).' KB';
    }

    /** Where this file is used, as label => count; empty when it is safe to delete. */
    public function usages(): array
    {
        return array_filter(array_map(
            fn (array $ref) => DB::table($ref[0])->where($ref[1], $this->id)->count(),
            self::REFERENCES,
        ));
    }

    /**
     * Ids of every upload used anywhere, in one UNION query - for marking
     * "in use" across a whole page of the library without a query per file.
     *
     * @return array<int, true> id => true, for isset() lookups
     */
    public static function idsInUse(): array
    {
        $query = null;

        foreach (self::REFERENCES as [$table, $column]) {
            $part = DB::table($table)->whereNotNull($column)->select("{$column} as id");
            $query = $query ? $query->union($part) : $part;
        }

        return array_fill_keys($query->pluck('id')->map(fn ($id) => (int) $id)->all(), true);
    }
}
