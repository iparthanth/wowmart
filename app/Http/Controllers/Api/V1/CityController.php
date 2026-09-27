<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CityRequest;
use App\Http\Resources\V1\CityResource;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CityController extends Controller
{
    public function __construct(private readonly CityService $cities) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return CityResource::collection(
            City::with('state.country')
                ->when($request->integer('state') > 0, fn ($q) => $q->where('state_id', $request->integer('state')))
                ->orderBy('name')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function store(CityRequest $request): CityResource
    {
        return new CityResource($this->cities->create($request->validated())); // 201
    }

    public function show(City $city): CityResource
    {
        return new CityResource($city->load('state.country'));
    }

    public function update(CityRequest $request, City $city): CityResource
    {
        return new CityResource($this->cities->update($city, $request->validated()));
    }

    public function destroy(City $city): Response
    {
        $this->cities->delete($city);

        return response()->noContent();
    }
}
