<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Admin\UploadController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/** Adding files to the media library, from the panel or the API (multipart files[]). */
class UploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'max:20'],
            // File::types checks the content, not just the name: a script renamed to .jpg fails.
            'files.*' => ['required', File::types(UploadController::TYPES)->max(UploadController::MAX_KB)],
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => 'Choose at least one file to upload.',
            'files.max' => 'Upload at most 20 files at a time.',
        ];
    }

    public function attributes(): array
    {
        return ['files.*' => 'file'];
    }
}
