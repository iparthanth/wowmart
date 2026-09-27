<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StateRequest;
use App\Http\Resources\V1\StateResource;
use App\Models\State;
use App\Services\StateService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StateController extends Controller
{
    public function __construct(private readonly StateService $states) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return StateResource::collection(
            State::with('country')
                ->when($request->integer('country') > 0, fn ($q) => $q->where('country_id', $request->integer('country')))
                ->orderBy('name')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function store(StateRequest $request): StateResource
    {
        return new StateResource($this->states->create($request->validated())); // 201
    }

    public function show(State $state): StateResource
    {
        return new StateResource($state->load('country'));
    }

    public function update(StateRequest $request, State $state): StateResource
    {
        return new StateResource($this->states->update($state, $request->validated()));
    }

    public function destroy(State $state): Response
    {
        $this->states->delete($state); // 409 while cities hang under it

        return response()->noContent();
    }
}
