<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RefundSettingsRequest;
use App\Models\RefundRequest;
use App\Services\RefundService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function __construct(private readonly RefundService $refunds) {}

    public function index(Request $request): View
    {
        $f = RefundRequest::filtersFrom($request->query());
        $f['status'] ??= RefundStatus::Pending; // the panel always shows one tab; pending is the work

        return view('admin.refunds.index', [
            'refunds' => RefundRequest::query()
                ->with(['order', 'orderDetail.product', 'customer', 'seller.shop'])
                ->filtered($f)
                ->paginate(20)
                ->withQueryString(),
            'counts' => RefundRequest::query()->toBase()
                ->selectRaw('refund_status, count(*) as n')
                ->groupBy('refund_status')
                ->pluck('n', 'refund_status'),
            'filters' => $f,
        ]);
    }

    public function approve(RefundRequest $refund): RedirectResponse
    {
        return $this->decide($refund, RefundStatus::Approved);
    }

    public function reject(RefundRequest $refund): RedirectResponse
    {
        return $this->decide($refund, RefundStatus::Rejected);
    }

    public function settings(): View
    {
        return view('admin.refunds.settings', ['days' => $this->refunds->requestDays()]);
    }

    public function updateSettings(RefundSettingsRequest $request): RedirectResponse
    {
        $days = (int) $request->validated('refund_request_time');
        $this->refunds->setRequestDays($days);

        return redirect()->route('admin.refunds.settings')
            ->with('status', "Customers can now ask for a refund up to {$days} ".str('day')->plural($days).' after delivery.');
    }

    private function decide(RefundRequest $refund, RefundStatus $to): RedirectResponse
    {
        try {
            $this->refunds->decide($refund, $to);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $amount = '৳'.number_format((float) $refund->refund_amount, 2);
        $code = $refund->order?->code;

        return back()->with('status', $to === RefundStatus::Approved
            ? "Refund of {$amount} for order {$code} approved. Pay the customer back by hand: cash-on-delivery money cannot be returned through WowMart."
            : "Refund of {$amount} for order {$code} rejected.");
    }
}
