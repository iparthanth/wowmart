<?php

namespace App\Http\Resources\V1;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One library file. "In use" is passed in by the caller (one idsInUse() query
 * per page) because asking each row would be a query per reference column per file.
 *
 * @mixin Upload
 */
class UploadResource extends JsonResource
{
    /** @param array<string, int>|null $usages label => count, only on the single-file view */
    public function __construct(Upload $upload, private readonly bool $inUse = false, private readonly ?array $usages = null)
    {
        parent::__construct($upload);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->file_original_name,
            'url' => $this->url(),
            'type' => $this->type,
            'extension' => $this->extension,
            'size_bytes' => $this->file_size,
            'size' => $this->humanSize(),
            'in_use' => $this->inUse,
            'usages' => $this->when($this->usages !== null, fn () => collect($this->usages)
                ->map(fn (int $count, string $label) => ['label' => $label, 'count' => $count])
                ->values()),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
