<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NoteRequest;
use App\Http\Resources\V1\NoteResource;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NoteController extends Controller
{
    public function __construct(private readonly NoteService $notes) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return NoteResource::collection(
            Note::query()
                ->withCount(['refundProducts', 'warrantyProducts'])
                ->when(Note::typeFilter($request), fn ($q, string $t) => $q->where('type', $t))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function store(NoteRequest $request): NoteResource
    {
        return $this->show($this->notes->create($request->validated())); // 201
    }

    public function show(Note $note): NoteResource
    {
        return new NoteResource($note->loadCount(['refundProducts', 'warrantyProducts']));
    }

    /** PUT replaces the text; a different type is 422 (a note keeps its type). */
    public function update(NoteRequest $request, Note $note): NoteResource
    {
        return $this->show($this->notes->update($note, $request->validated()));
    }

    public function destroy(Note $note): Response
    {
        $this->notes->delete($note); // 409 while products use it

        return response()->noContent();
    }
}
