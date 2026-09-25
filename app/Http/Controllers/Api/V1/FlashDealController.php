<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FlashDealRequest;
use App\Http\Resources\V1\FlashDealResource;
use App\Models\FlashDeal;
use App\Services\FlashDealService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FlashDealController extends Controller
{
    public function __construct(private readonly FlashDealService $deals) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $deals = FlashDeal::with('bannerFile')
            ->withCount('products')
            ->latest('id')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return FlashDealResource::collection($deals);
    }

    public function store(FlashDealRequest $request): FlashDealResource
    {
        return $this->show($this->deals->create($request->validated())); // 201: model was just created
    }

    public function show(FlashDeal $flashDeal): FlashDealResource
    {
        return new FlashDealResource($flashDeal->load('bannerFile', 'products'));
    }

    /** PUT replaces the whole deal, product list included. */
    public function update(FlashDealRequest $request, FlashDeal $flashDeal): FlashDealResource
    {
        return $this->show($this->deals->update($flashDeal, $request->validated()));
    }

    public function flags(Request $request, FlashDeal $flashDeal): FlashDealResource
    {
        $flags = $request->validate([
            'status' => ['required_without:featured', 'boolean'],
            'featured' => ['required_without:status', 'boolean'],
        ]);

        return $this->show($this->deals->setFlags($flashDeal, array_map('boolval', $flags)));
    }

    public function destroy(FlashDeal $flashDeal): Response
    {
        $this->deals->delete($flashDeal);

        return response()->noContent();
    }
}
