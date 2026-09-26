<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CurrencyRequest;
use App\Http\Resources\V1\CurrencyResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CurrencyController extends Controller
{
    public function __construct(private readonly CurrencyService $currencies) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return CurrencyResource::collection(
            Currency::orderBy('name')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function store(CurrencyRequest $request): CurrencyResource
    {
        return new CurrencyResource($this->currencies->create($request->validated())); // 201
    }

    public function show(Currency $currency): CurrencyResource
    {
        return new CurrencyResource($currency);
    }

    public function update(CurrencyRequest $request, Currency $currency): CurrencyResource
    {
        return new CurrencyResource($this->currencies->update($currency, $request->validated()));
    }

    public function destroy(Currency $currency): Response
    {
        $this->currencies->delete($currency); // 409 while it is the system currency

        return response()->noContent();
    }
}
