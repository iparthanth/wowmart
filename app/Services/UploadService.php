<?php

namespace App\Services;

use App\Models\Upload;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adding files to and removing them from the media library. Validation
 * (allowed types, size) happens before this, in the request.
 */
class UploadService
{
    private const DISK = 'uploads';

    private const FOLDER = 'all';

    public function store(UploadedFile $file, User $by): Upload
    {
        // Size first: the temporary file is gone once it has been moved.
        $size = $file->getSize();

        // hashName() is 40 random characters plus an extension guessed from
        // the file's content, so the client's file name never reaches the disk.
        $path = $file->store(self::FOLDER, self::DISK);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return Upload::create([
            'file_original_name' => Str::limit(basename($file->getClientOriginalName()), 250, ''),
            'file_name' => 'uploads/'.$path,
            'user_id' => $by->id,
            'file_size' => $size,
            'extension' => $extension,
            'type' => $extension === 'pdf' ? 'document' : 'image',
        ]);
    }

    /**
     * @throws DomainException when something still uses the file
     */
    public function delete(Upload $upload): void
    {
        if ($used = $upload->usages()) {
            $where = collect($used)->map(fn (int $n, string $label) => $n.' '.Str::plural($label, $n))->implode(', ');

            throw new DomainException("\"{$upload->file_original_name}\" is still used ({$where}). Replace it there first.");
        }

        // Row first: if removing the file then fails, the leftover is an unused
        // file on disk rather than a library entry pointing at nothing.
        $upload->delete();

        // The disk is rooted at public/uploads, so a path can never reach outside it.
        Storage::disk(self::DISK)->delete(Str::after($upload->file_name, 'uploads/'));
    }
}
