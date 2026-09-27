<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryBoyHistoryRequest;
use App\Http\Requests\Admin\DeliveryBoyRequest;
use App\Models\DeliveryBoyCollection;
use App\Models\DeliveryBoyPayment;
use App\Models\Order;
use App\Models\User;
use App\Services\DeliveryBoyService;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryBoyController extends Controller
{
    public function __construct(private readonly DeliveryBoyService $boys) {}

    /** The list, with the "Add a delivery boy" form beside it. */
    public function index(): View
    {
        return view('admin.delivery-boys.index', [
            'boys' => User::where('user_type', UserType::DeliveryBoy)->orderBy('name')->paginate(20),
            'newBoy' => new User,
        ]);
    }

    public function store(DeliveryBoyRequest $request): RedirectResponse
    {
        $boy = $this->boys->create($request->payload());

        return redirect()->route('admin.delivery-boys.index')->with('status', "Delivery boy \"{$boy->name}\" added.");
    }

    public function edit(User $delivery_boy): View
    {
        return view('admin.delivery-boys.edit', ['boy' => $delivery_boy]);
    }

    public function update(DeliveryBoyRequest $request, User $delivery_boy): RedirectResponse
    {
        $this->boys->update($delivery_boy, $request->payload());

        return redirect()->route('admin.delivery-boys.index')->with('status', 'Changes saved.');
    }

    public function destroy(User $delivery_boy): RedirectResponse
    {
        try {
            $this->boys->delete($delivery_boy);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.delivery-boys.index')->with('status', "Delivery boy \"{$delivery_boy->name}\" deleted.");
    }

    public function payments(): View
    {
        return $this->historyView('payments');
    }

    public function collections(): View
    {
        return $this->historyView('collections');
    }

    /** One view serves both histories - same shape, different rows and wording. */
    private function historyView(string $kind): View
    {
        $boys = User::where('user_type', UserType::DeliveryBoy)->orderBy('name')->get(['id', 'name']);
        $isPayments = $kind === 'payments';

        return view('admin.delivery-boys.history', [
            'kind' => $kind,
            'rows' => $isPayments
                ? DeliveryBoyPayment::with('deliveryBoy:id,name')->orderBy('paid_at', 'desc')->paginate(20)
                : DeliveryBoyCollection::with('deliveryBoy:id,name')->orderBy('collected_at', 'desc')->paginate(20),
            'boys' => $boys,
        ]);
    }

    public function storePayment(DeliveryBoyHistoryRequest $request): RedirectResponse
    {
        $this->boys->pay($request->payload());

        return redirect()->route('admin.delivery-boys.payments')->with('status', 'Payment recorded.');
    }

    public function storeCollection(DeliveryBoyHistoryRequest $request): RedirectResponse
    {
        $this->boys->collect($request->payload());

        return redirect()->route('admin.delivery-boys.collections')->with('status', 'Collection recorded.');
    }

    /** Orders whose delivery boy asked to cancel, newest request first. */
    public function cancelRequests(): View
    {
        return view('admin.delivery-boys.cancel-requests', [
            'orders' => Order::with(['deliveryBoy:id,name', 'customer:id,name'])
                ->whereNotNull('cancel_requested_at')
                ->orderByDesc('cancel_requested_at')
                ->paginate(20),
        ]);
    }

    public function approveCancel(OrderService $orders, Order $order): RedirectResponse
    {
        try {
            $this->boys->approveCancel($order, $orders);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.delivery-boys.cancel-requests')
            ->with('status', "Order {$order->code} cancelled; its items are back in stock.");
    }

    public function declineCancel(Order $order): RedirectResponse
    {
        try {
            $this->boys->declineCancel($order);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.delivery-boys.cancel-requests')
            ->with('status', "The cancel request for order {$order->code} was declined.");
    }
}
