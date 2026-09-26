<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaxRequest;
use App\Http\Resources\V1\TaxResource;
use App\Models\Tax;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaxController extends Controller
{
    public function __construct(private readonly TaxService $taxes) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return TaxResource::collection(
            Tax::withCount('productTaxes')
                ->orderBy('name')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function store(TaxRequest $request): TaxResource
    {
        return new TaxResource($this->taxes->create($request->validated())); // 201
    }

    public function show(Tax $tax): TaxResource
    {
        return new TaxResource($tax->loadCount('productTaxes'));
    }

    public function update(TaxRequest $request, Tax $tax): TaxResource
    {
        return new TaxResource($this->taxes->update($tax, $request->validated()));
    }

    public function destroy(Tax $tax): Response
    {
        $this->taxes->delete($tax); // 409 while products are assigned it

        return response()->noContent();
    }
}
