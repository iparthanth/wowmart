<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentGatewayRequest;
use App\Http\Resources\V1\ManualPaymentMethodResource;
use App\Http\Resources\V1\OfflinePackagePaymentResource;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\WalletRechargeResource;
use App\Models\ManualPaymentMethod;
use App\Models\OfflinePackagePayment;
use App\Models\Order;
use App\Models\WalletRecharge;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PaymentGatewayController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $gateways) {}

    // ------------------------------------------------------- gateway settings

    /** Every gateway card's stored credentials, keyed by gateway slug. */
    public function gatewaysIndex(): array
    {
        $settings = [];
        foreach (array_keys(PaymentGatewayService::GATEWAYS) as $gateway) {
            $settings[$gateway] = PaymentGatewayService::gatewaySettings($gateway);
        }

        return ['data' => $settings];
    }

    public function gatewaysShow(string $gateway): array
    {
        abort_unless(array_key_exists($gateway, PaymentGatewayService::GATEWAYS), 404);

        return ['data' => PaymentGatewayService::gatewaySettings($gateway)];
    }

    public function gatewaysUpdate(PaymentGatewayRequest $request, string $gateway): array
    {
        abort_unless(array_key_exists($gateway, PaymentGatewayService::GATEWAYS), 404);
        $this->gateways->saveGateway($gateway, $request->validated());

        return ['data' => PaymentGatewayService::gatewaySettings($gateway)];
    }

    // ---------------------------------------------------- manual payment methods

    public function manualIndex(Request $request): AnonymousResourceCollection
    {
        return ManualPaymentMethodResource::collection(
            ManualPaymentMethod::query()
                ->with('logoUpload')
                ->when($request->query('status') !== null && $request->query('status') !== '', fn ($q) => $q->where('status', (bool) $request->query('status')))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function manualShow(ManualPaymentMethod $manualPaymentMethod): ManualPaymentMethodResource
    {
        return new ManualPaymentMethodResource($manualPaymentMethod->load('logoUpload'));
    }

    public function manualStore(PaymentGatewayRequest $request): ManualPaymentMethodResource
    {
        $method = $this->gateways->saveManualMethod($request->validated() + ['status' => (int) $request->boolean('status')]);

        return new ManualPaymentMethodResource($method->load('logoUpload'));
    }

    public function manualUpdate(PaymentGatewayRequest $request, ManualPaymentMethod $manualPaymentMethod): ManualPaymentMethodResource
    {
        $this->gateways->saveManualMethod($request->validated() + ['status' => (int) $request->boolean('status')], $manualPaymentMethod);

        return new ManualPaymentMethodResource($manualPaymentMethod->load('logoUpload'));
    }

    public function manualDestroy(ManualPaymentMethod $manualPaymentMethod): Response
    {
        $manualPaymentMethod->delete();

        return response()->noContent();
    }

    // ---------------------------------------------------------- offline orders

    public function offlineOrdersIndex(Request $request): AnonymousResourceCollection
    {
        return OrderResource::collection(
            Order::query()
                ->with(['customer', 'seller'])
                ->withCount('details')
                ->whereIn('payment_type', ['manual_payment', 'offline'])
                ->when($request->query('payment_status'), fn ($q, $s) => $q->where('payment_status', $s))
                ->when($request->query('delivery_status'), fn ($q, $s) => $q->where('delivery_status', $s))
                ->when($request->query('date'), fn ($q, $d) => $q->whereDate('created_at', $d))
                ->when($request->query('search'), fn ($q, $s) => $q->where('code', 'like', "%{$s}%"))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function offlineOrdersShow(Order $order): OrderResource
    {
        return new OrderResource($order->load(['customer', 'seller', 'details']));
    }

    public function offlineOrdersMarkPaid(PaymentGatewayRequest $request, Order $order): OrderResource
    {
        return new OrderResource($this->gateways->markOrderPaid($order)->load(['customer', 'seller'])->loadCount('details')); // 409 when already paid
    }

    // ------------------------------------------------- offline wallet recharges

    public function walletIndex(Request $request): AnonymousResourceCollection
    {
        return WalletRechargeResource::collection(
            WalletRecharge::query()
                ->with(['customer', 'receipt'])
                ->when($request->query('approval'), fn ($q, $a) => $q->where('approval', $a))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function walletShow(WalletRecharge $recharge): WalletRechargeResource
    {
        return new WalletRechargeResource($recharge->load(['customer', 'receipt']));
    }

    public function walletDecision(PaymentGatewayRequest $request, WalletRecharge $recharge): WalletRechargeResource
    {
        return new WalletRechargeResource(
            $this->gateways->decideRecharge($recharge, $request->validated('approval'))->load(['customer', 'receipt']), // 409 when already decided
        );
    }

    // ------------------------------------------- offline package payments x2

    public function packagesIndex(Request $request): AnonymousResourceCollection
    {
        $type = $request->query('type', 'customer');
        abort_unless(in_array($type, OfflinePackagePayment::TYPES, true), 404);

        return OfflinePackagePaymentResource::collection(
            OfflinePackagePayment::query()
                ->with(['user', 'receipt'])
                ->where('package_type', $type)
                ->when($request->query('approval'), fn ($q, $a) => $q->where('approval', $a))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function packagesShow(OfflinePackagePayment $packagePayment): OfflinePackagePaymentResource
    {
        return new OfflinePackagePaymentResource($packagePayment->load(['user', 'receipt']));
    }

    public function packagesDecision(PaymentGatewayRequest $request, OfflinePackagePayment $packagePayment): OfflinePackagePaymentResource
    {
        return new OfflinePackagePaymentResource(
            $this->gateways->decidePackagePayment($packagePayment, $request->validated('approval'))->load(['user', 'receipt']), // 409 when already decided
        );
    }
}
