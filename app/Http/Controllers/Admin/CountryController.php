<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryRequest;
use App\Models\Country;
use App\Services\CountryService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function __construct(private readonly CountryService $countries) {}

    /** The list, with the "Add a country" form beside it. */
    public function index(): View
    {
        return view('admin.countries.index', [
            'countries' => Country::withCount('states')->orderBy('name')->paginate(20),
            'newCountry' => new Country,
        ]);
    }

    public function store(CountryRequest $request): RedirectResponse
    {
        $country = $this->countries->create($request->validated());

        return redirect()->route('admin.countries.index')->with('status', "Country \"{$country->name}\" added.");
    }

    public function edit(Country $country): View
    {
        return view('admin.countries.edit', ['country' => $country]);
    }

    public function update(CountryRequest $request, Country $country): RedirectResponse
    {
        $this->countries->update($country, $request->validated());

        return redirect()->route('admin.countries.edit', $country)->with('status', 'Changes saved.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        try {
            $this->countries->delete($country);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.countries.index')->with('status', "Country \"{$country->name}\" deleted.");
    }
}
