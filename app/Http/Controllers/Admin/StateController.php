<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StateRequest;
use App\Models\Country;
use App\Models\State;
use App\Services\StateService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StateController extends Controller
{
    public function __construct(private readonly StateService $states) {}

    /** The list, filtered by country from the query string, with the add form beside it. */
    public function index(Request $request): View
    {
        $countryId = $request->integer('country');

        return view('admin.states.index', [
            'states' => State::with('country')
                ->when($countryId > 0, fn ($q) => $q->where('country_id', $countryId))
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'newState' => new State,
            'countries' => Country::orderBy('name')->get(),
            'countryId' => $countryId,
        ]);
    }

    public function store(StateRequest $request): RedirectResponse
    {
        $state = $this->states->create($request->validated());

        return redirect()->route('admin.states.index')->with('status', "State \"{$state->name}\" added.");
    }

    public function edit(State $state): View
    {
        return view('admin.states.edit', [
            'state' => $state,
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function update(StateRequest $request, State $state): RedirectResponse
    {
        $this->states->update($state, $request->validated());

        return redirect()->route('admin.states.edit', $state)->with('status', 'Changes saved.');
    }

    public function destroy(State $state): RedirectResponse
    {
        try {
            $this->states->delete($state);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.states.index')->with('status', "State \"{$state->name}\" deleted.");
    }
}
