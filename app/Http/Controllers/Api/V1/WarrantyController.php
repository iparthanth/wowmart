<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WarrantyRequest;
use App\Http\Resources\V1\WarrantyResource;
use App\Models\Warranty;
use App\Services\WarrantyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WarrantyController extends Controller
{
    public function __construct(private readonly WarrantyService $warranties) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return WarrantyResource::collection(
            Warranty::withCount('products')->orderBy('text')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function store(WarrantyRequest $request): WarrantyResource
    {
        return $this->show($this->warranties->create($request->validated())); // 201
    }

    public function show(Warranty $warranty): WarrantyResource
    {
        return new WarrantyResource($warranty->loadCount('products'));
    }

    public function update(WarrantyRequest $request, Warranty $warranty): WarrantyResource
    {
        return $this->show($this->warranties->update($warranty, $request->validated()));
    }

    public function destroy(Warranty $warranty): Response
    {
        $this->warranties->delete($warranty); // 409 while products use it

        return response()->noContent();
    }
}
