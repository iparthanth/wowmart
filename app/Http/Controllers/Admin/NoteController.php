<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NoteRequest;
use App\Models\Note;
use App\Services\NoteService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __construct(private readonly NoteService $notes) {}

    public function index(Request $request): View
    {
        $type = Note::typeFilter($request);

        return view('admin.notes.index', [
            'notes' => Note::query()
                ->withCount(['refundProducts', 'warrantyProducts'])
                ->when($type, fn ($q, string $t) => $q->where('type', $t))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'type' => $type,
        ]);
    }

    public function create(): View
    {
        return view('admin.notes.form', ['note' => new Note]);
    }

    public function store(NoteRequest $request): RedirectResponse
    {
        $this->notes->create($request->validated());

        return redirect()->route('admin.notes.index')->with('status', 'Note added.');
    }

    public function edit(Note $note): View
    {
        return view('admin.notes.form', ['note' => $note->loadCount(['refundProducts', 'warrantyProducts'])]);
    }

    public function update(NoteRequest $request, Note $note): RedirectResponse
    {
        $this->notes->update($note, $request->validated());

        return redirect()->route('admin.notes.edit', $note)->with('status', 'Changes saved.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        try {
            $this->notes->delete($note);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.notes.index')->with('status', 'Note deleted.');
    }
}
