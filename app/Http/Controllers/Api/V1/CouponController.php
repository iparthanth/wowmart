<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Http\Resources\V1\CouponResource;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $coupons) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $coupons = Coupon::withCount('usages')
            ->latest('id')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return CouponResource::collection($coupons);
    }

    public function store(CouponRequest $request): CouponResource
    {
        return $this->show($this->coupons->create($request->validated(), $request->user())); // 201: model was just created
    }

    public function show(Coupon $coupon): CouponResource
    {
        return new CouponResource($coupon->loadCount('usages'));
    }

    /** PUT replaces the whole coupon with the panel form's rules; the type must stay the same (422). */
    public function update(CouponRequest $request, Coupon $coupon): CouponResource
    {
        return $this->show($this->coupons->update($coupon, $request->validated()));
    }

    public function flags(Request $request, Coupon $coupon): CouponResource
    {
        $flags = $request->validate(['status' => ['required', 'boolean']]);

        return $this->show($this->coupons->setFlags($coupon, ['status' => (bool) $flags['status']]));
    }

    public function destroy(Coupon $coupon): Response
    {
        $this->coupons->delete($coupon); // 409 once it has been used

        return response()->noContent();
    }
}
