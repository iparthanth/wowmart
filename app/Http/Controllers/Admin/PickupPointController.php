<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PickupPointRequest;
use App\Models\PickupPoint;
use App\Services\PickupPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PickupPointController extends Controller
{
    public function __construct(private readonly PickupPointService $points) {}

    /** The list, with the "Add a pickup point" form beside it, like colours. */
    public function index(): View
    {
        return view('admin.pickup-points.index', [
            'points' => PickupPoint::orderBy('name')->paginate(20),
            'newPoint' => new PickupPoint,
        ]);
    }

    public function store(PickupPointRequest $request): RedirectResponse
    {
        $point = $this->points->create($request->payload());

        return redirect()->route('admin.pickup-points.index')->with('status', "Pickup point \"{$point->name}\" added.");
    }

    public function edit(PickupPoint $pickup_point): View
    {
        return view('admin.pickup-points.edit', ['point' => $pickup_point]);
    }

    public function update(PickupPointRequest $request, PickupPoint $pickup_point): RedirectResponse
    {
        $this->points->update($pickup_point, $request->payload());

        return redirect()->route('admin.pickup-points.index')->with('status', 'Changes saved.');
    }

    public function destroy(PickupPoint $pickup_point): RedirectResponse
    {
        $this->points->delete($pickup_point);

        return redirect()->route('admin.pickup-points.index')->with('status', "Pickup point \"{$pickup_point->name}\" deleted.");
    }
}
