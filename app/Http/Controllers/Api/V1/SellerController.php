<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SellerResource;
use App\Models\User;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SellerController extends Controller
{
    public function __construct(private readonly SellerService $sellers) {}

    /** Same filters as the panel list (SellerService::filtersFrom). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $sellers = $this->sellers->query(SellerService::filtersFrom($request->query()))
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return SellerResource::collection($sellers);
    }

    public function show(User $seller): SellerResource
    {
        return new SellerResource($this->sellers->find($seller));
    }

    public function verification(Request $request, User $seller): SellerResource
    {
        $status = $request->validate(
            ['status' => ['required', 'string', 'in:approved,rejected']],
            ['status.in' => 'Choose approved or rejected.'],
        )['status'];

        $this->sellers->setVerification($seller, $status); // 409 when the seller has no shop

        return $this->show($seller);
    }

    public function flags(Request $request, User $seller): SellerResource
    {
        $flags = $request->validate(['banned' => ['required', 'boolean']]);

        $this->sellers->setFlags($seller, ['banned' => (bool) $flags['banned']]);

        return $this->show($seller);
    }

    public function destroy(User $seller): Response
    {
        $this->sellers->delete($seller); // 409 while products, order lines or history exist

        return response()->noContent();
    }
}
