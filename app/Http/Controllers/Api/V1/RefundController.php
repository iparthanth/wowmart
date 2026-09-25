<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RefundSettingsRequest;
use App\Http\Resources\V1\RefundResource;
use App\Models\RefundRequest;
use App\Services\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    public function __construct(private readonly RefundService $refunds) {}

    /** ?status and ?search, as in the panel; no status means every status. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $refunds = RefundRequest::query()
            ->with(['order', 'orderDetail.product', 'customer', 'seller.shop'])
            ->filtered(RefundRequest::filtersFrom($request->query()))
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return RefundResource::collection($refunds);
    }

    public function show(RefundRequest $refund): RefundResource
    {
        return new RefundResource($refund->load(['order', 'orderDetail.product', 'customer', 'seller.shop']));
    }

    /** 409 unless the refund is still pending (DomainException from the service). */
    public function status(Request $request, RefundRequest $refund): RefundResource
    {
        $status = $request->validate([
            'status' => ['required', Rule::enum(RefundStatus::class)->only([RefundStatus::Approved, RefundStatus::Rejected])],
        ])['status'];

        return $this->show($this->refunds->decide($refund, RefundStatus::from($status)));
    }

    public function settings(): JsonResponse
    {
        return response()->json(['data' => ['refund_request_time' => $this->refunds->requestDays()]]);
    }

    public function updateSettings(RefundSettingsRequest $request): JsonResponse
    {
        $this->refunds->setRequestDays((int) $request->validated('refund_request_time'));

        return $this->settings();
    }
}
