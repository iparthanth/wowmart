<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryBoyHistoryRequest;
use App\Http\Requests\Admin\DeliveryBoyRequest;
use App\Http\Resources\V1\DeliveryBoyHistoryResource;
use App\Http\Resources\V1\DeliveryBoyResource;
use App\Models\DeliveryBoyCollection;
use App\Models\DeliveryBoyPayment;
use App\Models\User;
use App\Services\DeliveryBoyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DeliveryBoyController extends Controller
{
    public function __construct(private readonly DeliveryBoyService $boys) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return DeliveryBoyResource::collection(
            User::where('user_type', UserType::DeliveryBoy)
                ->orderBy('name')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function store(DeliveryBoyRequest $request): DeliveryBoyResource
    {
        return new DeliveryBoyResource($this->boys->create($request->payload())); // 201
    }

    public function show(User $delivery_boy): DeliveryBoyResource
    {
        return new DeliveryBoyResource($delivery_boy);
    }

    public function update(DeliveryBoyRequest $request, User $delivery_boy): DeliveryBoyResource
    {
        return new DeliveryBoyResource($this->boys->update($delivery_boy, $request->payload()));
    }

    public function destroy(User $delivery_boy): Response
    {
        $this->boys->delete($delivery_boy); // 409 while history rows exist

        return response()->noContent();
    }

    public function payments(Request $request): AnonymousResourceCollection
    {
        return DeliveryBoyHistoryResource::collection(
            DeliveryBoyPayment::with('deliveryBoy:id,name')
                ->orderBy('paid_at', 'desc')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function collections(Request $request): AnonymousResourceCollection
    {
        return DeliveryBoyHistoryResource::collection(
            DeliveryBoyCollection::with('deliveryBoy:id,name')
                ->orderBy('collected_at', 'desc')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function storePayment(DeliveryBoyHistoryRequest $request): DeliveryBoyHistoryResource
    {
        return new DeliveryBoyHistoryResource($this->boys->pay($request->payload())); // 201
    }

    public function storeCollection(DeliveryBoyHistoryRequest $request): DeliveryBoyHistoryResource
    {
        return new DeliveryBoyHistoryResource($this->boys->collect($request->payload())); // 201
    }
}
