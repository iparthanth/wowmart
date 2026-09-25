<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    /** Same filters as the panel list (Order::filtersFrom). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with(['customer', 'seller.shop'])
            ->withCount('details')
            ->filtered(Order::filtersFrom($request->query()))
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(['customer', 'seller.shop', 'details.product'])->loadCount('details'));
    }

    /** 409 when the transition is not allowed (DomainException from the service). */
    public function deliveryStatus(Request $request, Order $order): OrderResource
    {
        $status = $request->validate(['status' => ['required', Rule::enum(DeliveryStatus::class)]])['status'];

        return $this->show($this->orders->changeDeliveryStatus($order, DeliveryStatus::from($status)));
    }

    public function paymentStatus(Request $request, Order $order): OrderResource
    {
        $status = $request->validate(['status' => ['required', Rule::enum(PaymentStatus::class)]])['status'];

        return $this->show($this->orders->setPaymentStatus($order, PaymentStatus::from($status)));
    }
}
