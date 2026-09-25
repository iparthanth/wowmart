<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Http\Resources\V1\CustomerResource;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Customers (users with user_type "customer"). An admin or seller id is a 404.
 *
 * password is write-only: required on create, optional on PUT (omitted or null
 * keeps the current one), always with password_confirmation, never returned.
 */
class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customers) {}

    /** ?search, ?status=active|banned, ?verification=verified|unverified, ?sort=newest|oldest|spent */
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = User::customers()
            ->withCount('orders')
            ->withTotalSpent()
            ->customerFiltered(User::customerFiltersFrom($request->query()))
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return CustomerResource::collection($customers);
    }

    public function store(CustomerRequest $request): CustomerResource
    {
        return $this->show($this->customers->create($request->validated())); // 201: model was just created
    }

    /** With the address book and the last 10 orders. */
    public function show(User $customer): CustomerResource
    {
        $fresh = User::whereKey($customer->id)
            ->withCount(['orders', 'wishlists', 'reviews'])
            ->withTotalSpent()
            ->with(['addresses', 'orders' => fn ($q) => $q->latest('id')->limit(10)])
            ->firstOrFail();
        $fresh->wasRecentlyCreated = $customer->wasRecentlyCreated;

        return new CustomerResource($fresh);
    }

    /** PUT replaces the whole profile, with the same rules as the panel's form. */
    public function update(CustomerRequest $request, User $customer): CustomerResource
    {
        return $this->show($this->customers->update($customer, $request->validated()));
    }

    public function flags(Request $request, User $customer): CustomerResource
    {
        $flags = $request->validate(['banned' => ['required', 'boolean']]);

        return $this->show($this->customers->setFlags($customer, ['banned' => (bool) $flags['banned']]));
    }

    public function destroy(User $customer): Response
    {
        $this->customers->delete($customer); // 409 while the customer has orders

        return response()->noContent();
    }
}
