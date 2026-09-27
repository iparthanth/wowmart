<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ZoneRequest;
use App\Models\Country;
use App\Models\Zone;
use App\Services\ZoneService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ZoneController extends Controller
{
    public function __construct(private readonly ZoneService $zones) {}

    /** The list, with the "Add a zone" form beside it. */
    public function index(): View
    {
        return view('admin.zones.index', [
            'zones' => Zone::withCount('countries')->orderBy('name')->paginate(20),
            'newZone' => new Zone,
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    /** @return RedirectResponse $request->payload() is [row, country ids] */
    public function store(ZoneRequest $request): RedirectResponse
    {
        [$data, $countryIds] = $request->payload();
        $zone = $this->zones->create($data, $countryIds);

        return redirect()->route('admin.zones.index')->with('status', "Zone \"{$zone->name}\" added.");
    }

    public function edit(Zone $zone): View
    {
        return view('admin.zones.edit', [
            'zone' => $zone,
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function update(ZoneRequest $request, Zone $zone): RedirectResponse
    {
        [$data, $countryIds] = $request->payload();
        $this->zones->update($zone, $data, $countryIds);

        return redirect()->route('admin.zones.edit', $zone)->with('status', 'Changes saved.');
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        try {
            $this->zones->delete($zone);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.zones.index')->with('status', "Zone \"{$zone->name}\" deleted.");
    }
}
