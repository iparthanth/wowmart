<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Services\SellerService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerController extends Controller
{
    public function __construct(private readonly SellerService $sellers) {}

    public function index(Request $request): View
    {
        $f = SellerService::filtersFrom($request->query());

        return view('admin.sellers.index', [
            'sellers' => $this->sellers->query($f)->paginate(20)->withQueryString(),
            'filters' => $f,
            'filtered' => $f['search'] !== '' || $f['verification'] || $f['status'],
        ]);
    }

    public function show(User $seller): View
    {
        return view('admin.sellers.show', [
            'seller' => $this->sellers->find($seller),
            'products' => Product::where('user_id', $seller->id)->with('thumbnail')->latest('id')
                ->paginate(10, pageName: 'products'),
            'lines' => OrderDetail::where('seller_id', $seller->id)->with(['order', 'product'])->latest('id')->limit(10)->get(),
        ]);
    }

    public function verification(Request $request, User $seller): RedirectResponse
    {
        $status = $request->validate(
            ['status' => ['required', 'string', 'in:approved,rejected']],
            ['status.in' => 'Choose approved or rejected.'],
        )['status'];

        $before = $seller->shop?->verification_status;

        try {
            $shop = $this->sellers->setVerification($seller, $status);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = $before === $status ? "{$shop->name} is already {$status}." : "{$shop->name} is now {$status}.";

        return redirect()->route('admin.sellers.show', $seller)->with('status', $message);
    }

    public function toggle(User $seller, string $field): RedirectResponse
    {
        $this->sellers->setFlags($seller, [$field => ! $seller->{$field}]);
        $name = $seller->shop?->name ?? $seller->name;

        return back()->with('status', $seller->banned
            ? "{$name} is banned. The storefront hides its products."
            : "{$name} is active again.");
    }

    public function destroy(User $seller): RedirectResponse
    {
        $name = $seller->shop?->name ?? $seller->name;

        try {
            $this->sellers->delete($seller);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.sellers.index')->with('status', "{$name} deleted.");
    }
}
