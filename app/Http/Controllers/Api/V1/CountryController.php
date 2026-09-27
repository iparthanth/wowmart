<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryRequest;
use App\Http\Resources\V1\CountryResource;
use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CountryController extends Controller
{
    public function __construct(private readonly CountryService $countries) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return CountryResource::collection(
            Country::withCount('states')->orderBy('name')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function store(CountryRequest $request): CountryResource
    {
        return new CountryResource($this->countries->create($request->validated())); // 201
    }

    public function show(Country $country): CountryResource
    {
        return new CountryResource($country);
    }

    public function update(CountryRequest $request, Country $country): CountryResource
    {
        return new CountryResource($this->countries->update($country, $request->validated()));
    }

    public function destroy(Country $country): Response
    {
        $this->countries->delete($country); // 409 while states hang under it

        return response()->noContent();
    }
}
