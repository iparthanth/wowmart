<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\User;
use App\Services\CustomerService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customers) {}

    public function index(Request $request): View
    {
        $f = User::customerFiltersFrom($request->query());

        return view('admin.customers.index', [
            'customers' => User::customers()
                ->withCount('orders')
                ->withTotalSpent()
                ->customerFiltered($f)
                ->paginate(20)
                ->withQueryString(),
            'filters' => $f,
            'filtered' => $f['search'] !== '' || $f['status'] || $f['verification'],
        ]);
    }

    public function show(User $customer): View
    {
        return view('admin.customers.show', [
            'customer' => User::whereKey($customer->id)
                ->withCount(['orders', 'wishlists', 'reviews'])
                ->withTotalSpent()
                ->with(['addresses' => fn ($q) => $q->orderByDesc('set_default')->orderBy('id')])
                ->firstOrFail(),
            'orders' => $customer->orders()->latest('id')->limit(10)->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.form', ['customer' => new User]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = $this->customers->create($request->validated());

        return redirect()->route('admin.customers.show', $customer)->with('status', "Customer \"{$customer->name}\" added.");
    }

    public function edit(User $customer): View
    {
        return view('admin.customers.form', ['customer' => $customer]);
    }

    public function update(CustomerRequest $request, User $customer): RedirectResponse
    {
        $this->customers->update($customer, $request->validated());

        return redirect()->route('admin.customers.show', $customer)->with('status', 'Changes saved.');
    }

    /** The button posts the value it wants, so a double click cannot undo itself. */
    public function toggle(Request $request, User $customer, string $field): RedirectResponse
    {
        $on = $request->validate([$field => ['required', 'boolean']])[$field];
        $customer = $this->customers->setFlags($customer, [$field => (bool) $on]);

        return back()->with('status', $customer->banned
            ? "{$customer->name} is banned and can no longer sign in or order."
            : "{$customer->name} can sign in and order again.");
    }

    public function destroy(User $customer): RedirectResponse
    {
        try {
            $this->customers->delete($customer);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.customers.index')->with('status', "Customer \"{$customer->name}\" deleted.");
    }
}
