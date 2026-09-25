<?php

namespace App\Http\Requests\Admin;

use App\Models\Note;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The note form (create and edit) and the API's create/replace body. */
class NoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the route group already requires an admin
    }

    protected function prepareForValidation(): void
    {
        // The edit form shows the type read-only and does not post it.
        $this->merge(['type' => $this->input('type', $this->route('note')?->type)]);
    }

    public function rules(): array
    {
        $note = $this->route('note');

        return [
            // A refund note switched to warranty would turn up under the wrong heading on every product using it.
            'type' => ['required', Rule::in(array_keys(Note::TYPES)), ...($note ? [Rule::in([$note->type])] : [])],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => $this->route('note')
                ? 'A note keeps its type. Add a new note for the other type instead.'
                : 'Choose refund or warranty.',
        ];
    }
}
