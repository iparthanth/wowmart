<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentGatewayRequest;
use App\Models\ManualPaymentMethod;
use App\Models\OfflinePackagePayment;
use App\Models\Order;
use App\Models\Upload;
use App\Models\WalletRecharge;
use App\Services\PaymentGatewayService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $gateways) {}

    // --------------------------------------------------------- payment methods

    public function index(): View
    {
        $settings = [];
        foreach (array_keys(PaymentGatewayService::GATEWAYS) as $gateway) {
            $settings[$gateway] = PaymentGatewayService::gatewaySettings($gateway);
        }

        return view('admin.payment-gateways.methods', [
            'settings' => $settings,
        ]);
    }

    public function saveGateway(PaymentGatewayRequest $request, string $gateway): RedirectResponse
    {
        if (! array_key_exists($gateway, PaymentGatewayService::GATEWAYS)) {
            abort(404);
        }

        $this->gateways->saveGateway($gateway, $request->validated());

        return back()->with('status', PaymentGatewayService::GATEWAYS[$gateway][0].' credentials saved.');
    }

    // ---------------------------------------------------- manual payment methods

    public function manualMethods(): View
    {
        return view('admin.payment-gateways.manual-methods', [
            'methods' => ManualPaymentMethod::query()->with('logoUpload')->latest('id')->get(),
            'images' => Upload::where('type', 'image')->latest('id')->get(),
        ]);
    }

    public function storeManualMethod(PaymentGatewayRequest $request): RedirectResponse
    {
        $this->gateways->saveManualMethod($request->validated() + ['status' => (int) $request->boolean('status')]);

        return back()->with('status', 'Manual payment method added.');
    }

    public function editManualMethod(ManualPaymentMethod $method): View
    {
        return view('admin.payment-gateways.manual-method-edit', [
            'method' => $method,
            'images' => Upload::where('type', 'image')->latest('id')->get(),
        ]);
    }

    public function updateManualMethod(PaymentGatewayRequest $request, ManualPaymentMethod $method): RedirectResponse
    {
        $this->gateways->saveManualMethod($request->validated() + ['status' => (int) $request->boolean('status')], $method);

        return redirect()->route('admin.manual-methods.index')->with('status', 'Manual payment method saved.');
    }

    public function destroyManualMethod(ManualPaymentMethod $method): RedirectResponse
    {
        $method->delete();

        return back()->with('status', 'Manual payment method deleted.');
    }

    // --------------------------------------------------------- offline orders

    public function offlineOrders(): View
    {
        $manualMethods = ManualPaymentMethod::pluck('name');

        return view('admin.payment-gateways.offline-orders', [
            'orders' => Order::query()
                ->with(['customer', 'seller'])
                ->withCount('details')
                ->whereIn('payment_type', ['manual_payment', 'offline'])
                ->when(request('payment_status'), fn ($q, $s) => $q->where('payment_status', $s))
                ->when(request('delivery_status'), fn ($q, $s) => $q->where('delivery_status', $s))
                ->when(request('date'), fn ($q, $d) => $q->whereDate('created_at', $d))
                ->when(request('search'), fn ($q, $s) => $q->where('code', 'like', "%{$s}%"))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'manualMethods' => $manualMethods,
        ]);
    }

    public function markOrderPaid(PaymentGatewayRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->gateways->markOrderPaid($order);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', "Order {$order->code} marked as paid.");
    }

    // ------------------------------------------------- offline wallet recharge

    public function offlineWallet(): View
    {
        return view('admin.payment-gateways.offline-wallet', [
            'recharges' => WalletRecharge::query()
                ->with(['customer', 'receipt'])
                ->when(request('approval'), fn ($q, $a) => $q->where('approval', $a))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function decideRecharge(PaymentGatewayRequest $request, WalletRecharge $recharge): RedirectResponse
    {
        try {
            $this->gateways->decideRecharge($recharge, $request->validated('approval'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $recharge->approval === 'approved' ? 'Recharge approved and wallet credited.' : 'Recharge request rejected.');
    }

    // ------------------------------------------- offline package payments x2

    public function offlinePackages(string $type): View
    {
        abort_unless(in_array($type, OfflinePackagePayment::TYPES, true), 404);

        return view('admin.payment-gateways.offline-packages', [
            'type' => $type,
            'payments' => OfflinePackagePayment::query()
                ->with(['user', 'receipt'])
                ->where('package_type', $type)
                ->when(request('approval'), fn ($q, $a) => $q->where('approval', $a))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function decidePackagePayment(PaymentGatewayRequest $request, OfflinePackagePayment $payment): RedirectResponse
    {
        try {
            $this->gateways->decidePackagePayment($payment, $request->validated('approval'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $payment->approval === 'approved' ? 'Package payment approved.' : 'Package payment rejected.');
    }
}
