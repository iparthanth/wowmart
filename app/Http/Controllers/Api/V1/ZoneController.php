<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ZoneRequest;
use App\Http\Resources\V1\ZoneResource;
use App\Models\Zone;
use App\Services\ZoneService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ZoneController extends Controller
{
    public function __construct(private readonly ZoneService $zones) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ZoneResource::collection(
            Zone::withCount('countries')->orderBy('name')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    /** 201; $request->payload() is [row, country ids]. */
    public function store(ZoneRequest $request): ZoneResource
    {
        [$data, $countryIds] = $request->payload();

        return new ZoneResource($this->zones->create($data, $countryIds)->load('countries'));
    }

    public function show(Zone $zone): ZoneResource
    {
        return new ZoneResource($zone->load('countries'));
    }

    public function update(ZoneRequest $request, Zone $zone): ZoneResource
    {
        [$data, $countryIds] = $request->payload();

        return new ZoneResource($this->zones->update($zone, $data, $countryIds)->load('countries'));
    }

    public function destroy(Zone $zone): Response
    {
        $this->zones->delete($zone); // 409 while carrier ranges price it

        return response()->noContent();
    }
}
