<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarrierRangeRequest;
use App\Http\Requests\Admin\CarrierRequest;
use App\Models\Carrier;
use App\Models\CarrierRange;
use App\Models\CarrierRangePrice;
use App\Models\Zone;
use App\Services\CarrierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CarrierController extends Controller
{
    public function __construct(private readonly CarrierService $carriers) {}

    /** The list, with the "Add a carrier" form beside it. */
    public function index(): View
    {
        return view('admin.carriers.index', [
            'carriers' => Carrier::withCount('ranges')->orderBy('name')->paginate(20),
            'newCarrier' => new Carrier,
        ]);
    }

    public function store(CarrierRequest $request): RedirectResponse
    {
        $carrier = $this->carriers->create($request->validated());

        return redirect()->route('admin.carriers.index')->with('status', "Carrier \"{$carrier->name}\" added.");
    }

    /** The carrier's billing setup: ranges and, inside each, its zone prices. */
    public function edit(Carrier $carrier): View
    {
        return view('admin.carriers.edit', [
            'carrier' => $carrier->load(['ranges.prices.zone']),
            'zones' => Zone::orderBy('name')->get(),
        ]);
    }

    public function update(CarrierRequest $request, Carrier $carrier): RedirectResponse
    {
        $this->carriers->update($carrier, $request->validated());

        return redirect()->route('admin.carriers.edit', $carrier)->with('status', 'Changes saved.');
    }

    public function destroy(Carrier $carrier): RedirectResponse
    {
        $this->carriers->delete($carrier);

        return redirect()->route('admin.carriers.index')->with('status', "Carrier \"{$carrier->name}\" deleted.");
    }

    public function storeRange(CarrierRangeRequest $request, Carrier $carrier): RedirectResponse
    {
        $this->carriers->addRange($carrier, $request->validated());

        return redirect()->route('admin.carriers.edit', $carrier)->with('status', 'Range added.');
    }

    public function destroyRange(CarrierRange $carrier_range): RedirectResponse
    {
        $this->carriers->deleteRange($carrier_range);

        return redirect()->route('admin.carriers.edit', $carrier_range->carrier)->with('status', 'Range deleted.');
    }

    public function storePrice(CarrierRangeRequest $request, CarrierRange $carrier_range): RedirectResponse
    {
        $this->carriers->setRangePrice($carrier_range, $request->validated());

        return redirect()->route('admin.carriers.edit', $carrier_range->carrier)->with('status', 'Zone price saved.');
    }

    public function destroyPrice(CarrierRangePrice $carrier_range_price): RedirectResponse
    {
        $this->carriers->deleteRangePrice($carrier_range_price);

        return redirect()->route('admin.carriers.edit', $carrier_range_price->range->carrier)->with('status', 'Zone price removed.');
    }
}
