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
        $f = Order::filtersFrom($request->query());

        return view('admin.orders.index', [
            'orders' => Order::query()
                ->with(['customer', 'seller.shop'])
                ->withCount('details')
                ->filtered($f)
                ->paginate(20)
                ->withQueryString(),
            'filters' => $f,
            'filtered' => $f['search'] !== '' || $f['delivery_status'] || $f['payment_status'] || $f['date_from'] || $f['date_to'],
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
