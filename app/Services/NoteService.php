<?php

namespace App\Services;

use App\Models\Note;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Every write to a note. Input is validated already (NoteRequest). */
class NoteService
{
    public function create(array $data): Note
    {
        return DB::transaction(fn () => Note::create($data));
    }

    /** Only the text changes; the type is fixed at creation (NoteRequest refuses another one). */
    public function update(Note $note, array $data): Note
    {
        return DB::transaction(fn () => tap($note)->update(['description' => $data['description']]));
    }

    /**
     * products.refund_note_id and warranty_note_id are nullOnDelete, so deleting a
     * note in use would silently blank those products' terms. Refuse instead.
     *
     * @throws DomainException while any product uses the note
     */
    public function delete(Note $note): void
    {
        DB::transaction(function () use ($note) {
            $count = $note->refundProducts()->count() + $note->warrantyProducts()->count();

            if ($count > 0) {
                throw new DomainException(sprintf(
                    '%d %s this note; pick another note on %s first.',
                    $count, $count === 1 ? 'product uses' : Str::plural('product', $count).' use', $count === 1 ? 'it' : 'them',
                ));
            }

            $note->delete();
        });
    }
}
