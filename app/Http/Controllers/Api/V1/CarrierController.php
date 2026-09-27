<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarrierRangeRequest;
use App\Http\Requests\Admin\CarrierRequest;
use App\Http\Resources\V1\CarrierResource;
use App\Models\Carrier;
use App\Models\CarrierRange;
use App\Models\CarrierRangePrice;
use App\Services\CarrierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CarrierController extends Controller
{
    public function __construct(private readonly CarrierService $carriers) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return CarrierResource::collection(
            Carrier::withCount('ranges')->orderBy('name')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function store(CarrierRequest $request): CarrierResource
    {
        return new CarrierResource($this->carriers->create($request->validated())); // 201
    }

    /** The carrier with its full billing setup. */
    public function show(Carrier $carrier): CarrierResource
    {
        return new CarrierResource($carrier->load('ranges.prices'));
    }

    public function update(CarrierRequest $request, Carrier $carrier): CarrierResource
    {
        return new CarrierResource($this->carriers->update($carrier, $request->validated()));
    }

    public function destroy(Carrier $carrier): Response
    {
        $this->carriers->delete($carrier);

        return response()->noContent();
    }

    public function storeRange(CarrierRangeRequest $request, Carrier $carrier): JsonResponse
    {
        $this->carriers->addRange($carrier, $request->validated());

        return (new CarrierResource($carrier->load('ranges.prices')))->response()->setStatusCode(201);
    }

    public function destroyRange(CarrierRange $carrier_range): Response
    {
        $this->carriers->deleteRange($carrier_range);

        return response()->noContent();
    }

    /** Sets one zone price inside a range, in place. */
    public function storePrice(CarrierRangeRequest $request, CarrierRange $carrier_range): JsonResponse
    {
        $this->carriers->setRangePrice($carrier_range, $request->validated());

        return (new CarrierResource($carrier_range->carrier->load('ranges.prices')))->response()->setStatusCode(201);
    }

    public function destroyPrice(CarrierRangePrice $carrier_range_price): Response
    {
        $this->carriers->deleteRangePrice($carrier_range_price);

        return response()->noContent();
    }
}
