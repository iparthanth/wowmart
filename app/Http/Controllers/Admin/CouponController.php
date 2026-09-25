<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CouponType;
use App\Enums\DiscountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CouponService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $coupons) {}

    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::withCount('usages')->latest('id')->paginate(20),
        ]);
    }

    /** No type yet (or an unknown one): pick a type first, like the reference panel - but as links, not AJAX. */
    public function create(Request $request): View
    {
        $type = is_string($t = $request->query('type')) ? CouponType::tryFrom($t) : null;

        if (! $type) {
            return view('admin.coupons.choose');
        }

        // Opens valid: active, starting now, running a month.
        $coupon = (new Coupon)->forceFill([
            'type' => $type,
            'discount_type' => DiscountType::Amount,
            'status' => true,
            'start_date' => now()->startOfHour(),
            'end_date' => now()->startOfHour()->addMonth(),
            'details' => [],
        ]);

        return $this->form($coupon);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        $coupon = $this->coupons->create($request->validated(), $request->user());

        return redirect()->route('admin.coupons.index')->with('status', "Coupon {$coupon->code} created.");
    }

    public function edit(Coupon $coupon): View
    {
        return $this->form($coupon->loadCount('usages'));
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->coupons->update($coupon, $request->validated());

        return redirect()->route('admin.coupons.edit', $coupon)->with('status', 'Changes saved.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        try {
            $this->coupons->delete($coupon);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.coupons.index')->with('status', "Coupon {$coupon->code} deleted.");
    }

    public function toggle(Coupon $coupon, string $field): RedirectResponse
    {
        $this->coupons->setFlags($coupon, [$field => ! $coupon->{$field}]);

        return back()->with('status', sprintf('Coupon %s is switched %s.', $coupon->code, $coupon->status ? 'on' : 'off'));
    }

    private function form(Coupon $coupon): View
    {
        return view('admin.coupons.form', [
            'coupon' => $coupon,
            // ponytail: every product in one scrollable list; add a search box when the catalogue outgrows it.
            'products' => $coupon->type === CouponType::ProductBase ? Product::orderBy('name')->get(['id', 'name', 'sku']) : collect(),
        ]);
    }
}
