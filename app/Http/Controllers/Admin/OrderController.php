<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): View
    {
        return $this->renderList($request, 'all', 'All orders');
    }

    public function inhouse(Request $request): View
    {
        return $this->renderList($request, 'inhouse', 'Inhouse orders');
    }

    public function sellerOrders(Request $request): View
    {
        return $this->renderList($request, 'seller', 'Seller orders');
    }

    public function byPickupPoint(Request $request): View
    {
        return $this->renderList($request, 'pickup', 'Pickup point orders');
    }

    public function unpaid(Request $request): View
    {
        return $this->renderList($request, 'unpaid', 'Unpaid orders');
    }

    /**
     * The Sales lists are fixed-filter views over the same order table, named
     * after the reference panel's sidebar. Each applies one base constraint the
     * visitor cannot remove; the shared filters (search, status, dates, sort)
     * then narrow further. A mixed cart has both in-house and seller lines, so
     * it shows in both of those lists - that matches how lines are picked and
     * packed.
     */
    private function renderList(Request $request, string $list, string $label): View
    {
        $f = Order::filtersFrom($request->query());

        $query = Order::query()
            ->with(['customer', 'seller.shop'])
            ->withCount('details');

        match ($list) {
            'inhouse' => $query->whereHas('details', fn ($d) => $d->whereNull('seller_id')),
            'seller' => $query->whereHas('details', fn ($d) => $d->whereNotNull('seller_id')),
            'pickup' => $query->whereHas('details', fn ($d) => $d->where('shipping_type', 'pickup_point')),
            'unpaid' => $query->where('payment_status', PaymentStatus::Unpaid->value),
            'all' => null,
        };

        return view('admin.orders.index', [
            'orders' => $query->filtered($f)->paginate(20)->withQueryString(),
            'filters' => $f,
            'filtered' => $f['search'] !== '' || $f['delivery_status'] || $f['payment_status'] || $f['date_from'] || $f['date_to'],
            'listLabel' => $label,
            'listRoute' => match ($list) {
                'inhouse' => 'admin.orders.inhouse',
                'seller' => 'admin.orders.seller',
                'pickup' => 'admin.orders.pickup',
                'unpaid' => 'admin.orders.unpaid',
                default => 'admin.orders.index',
            },
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['customer', 'seller.shop', 'details.product.thumbnail']),
        ]);
    }

    public function deliveryStatus(Request $request, Order $order): RedirectResponse
    {
        $to = DeliveryStatus::from($request->validate(['status' => ['required', Rule::enum(DeliveryStatus::class)]])['status']);

        try {
            $updated = $this->orders->changeDeliveryStatus($order, $to);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = "Order {$order->code} is now ".strtolower($to->label()).'.';

        if ($to === DeliveryStatus::Cancelled) {
            $message .= ' Its items are back in stock.';
        } elseif ($order->payment_status !== $updated->payment_status) {
            $message .= ' The courier collected the cash, so it is marked as paid.';
        }

        return redirect()->route('admin.orders.show', $updated)->with('status', $message);
    }

    public function paymentStatus(Request $request, Order $order): RedirectResponse
    {
        $to = PaymentStatus::from($request->validate(['status' => ['required', Rule::enum(PaymentStatus::class)]])['status']);

        try {
            $updated = $this->orders->setPaymentStatus($order, $to);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $updated)
            ->with('status', "Order {$order->code} is marked as ".strtolower($to->label()).'.');
    }
}
