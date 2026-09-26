<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PickupPointRequest;
use App\Http\Resources\V1\PickupPointResource;
use App\Models\PickupPoint;
use App\Services\PickupPointService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PickupPointController extends Controller
{
    public function __construct(private readonly PickupPointService $points) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return PickupPointResource::collection(
            PickupPoint::orderBy('name')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function store(PickupPointRequest $request): PickupPointResource
    {
        return new PickupPointResource($this->points->create($request->payload())); // 201
    }

    public function show(PickupPoint $pickup_point): PickupPointResource
    {
        return new PickupPointResource($pickup_point);
    }

    public function update(PickupPointRequest $request, PickupPoint $pickup_point): PickupPointResource
    {
        return new PickupPointResource($this->points->update($pickup_point, $request->payload()));
    }

    public function destroy(PickupPoint $pickup_point): Response
    {
        $this->points->delete($pickup_point);

        return response()->noContent();
    }
}
