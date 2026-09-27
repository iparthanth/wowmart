<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CityRequest;
use App\Models\City;
use App\Models\State;
use App\Services\CityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function __construct(private readonly CityService $cities) {}

    /** The list, filtered by state from the query string, with the add form beside it. */
    public function index(Request $request): View
    {
        $stateId = $request->integer('state');

        return view('admin.cities.index', [
            'cities' => City::with('state.country')
                ->when($stateId > 0, fn ($q) => $q->where('state_id', $stateId))
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'newCity' => new City,
            'states' => State::with('country')->orderBy('name')->get(),
            'stateId' => $stateId,
        ]);
    }

    public function store(CityRequest $request): RedirectResponse
    {
        $city = $this->cities->create($request->validated());

        return redirect()->route('admin.cities.index')->with('status', "City \"{$city->name}\" added.");
    }

    public function edit(City $city): View
    {
        return view('admin.cities.edit', [
            'city' => $city,
            'states' => State::with('country')->orderBy('name')->get(),
        ]);
    }

    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $this->cities->update($city, $request->validated());

        return redirect()->route('admin.cities.edit', $city)->with('status', 'Changes saved.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->cities->delete($city);

        return redirect()->route('admin.cities.index')->with('status', "City \"{$city->name}\" deleted.");
    }
}
