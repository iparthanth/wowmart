<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AffiliateRequest;
use App\Models\Affiliate;
use App\Models\AffiliateLog;
use App\Models\AffiliateReferral;
use App\Models\AffiliateWithdrawal;
use App\Models\Category;
use App\Services\AffiliateService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function __construct(private readonly AffiliateService $affiliates) {}

    // --------------------------------------------------- configurations screen

    public function index(): View
    {
        return view('admin.affiliate.configuration', [
            'settings' => AffiliateService::settings(),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function saveConfigurations(AffiliateRequest $request): RedirectResponse
    {
        try {
            $this->affiliates->saveConfigurations($request->validated());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Affiliate configurations saved.');
    }

    // ------------------------------------------------ registration form screen

    public function registration(): View
    {
        return view('admin.affiliate.registration', [
            'settings' => AffiliateService::settings(),
        ]);
    }

    public function saveRegistration(AffiliateRequest $request): RedirectResponse
    {
        $this->affiliates->saveRegistrationForm($request->validated());

        return back()->with('status', 'Registration form saved.');
    }

    // ------------------------------------------------------------------ lists

    public function users(): View
    {
        return view('admin.affiliate.users', [
            'affiliates' => Affiliate::query()
                ->with('user')
                ->withCount('referrals')
                ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function referrals(): View
    {
        return view('admin.affiliate.referrals', [
            'referrals' => AffiliateReferral::query()
                ->with(['referredUser', 'affiliate.user'])
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function withdrawals(): View
    {
        return view('admin.affiliate.withdrawals', [
            'withdrawals' => AffiliateWithdrawal::query()
                ->with('affiliate.user')
                ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function logs(): View
    {
        return view('admin.affiliate.logs', [
            'logs' => AffiliateLog::query()
                ->with(['affiliate.user', 'referredUser', 'order'])
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    // --------------------------------------------------------------- decisions

    public function decideAffiliate(AffiliateRequest $request, Affiliate $affiliate): RedirectResponse
    {
        try {
            $this->affiliates->decideAffiliate($affiliate, $request->validated('status'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', "Affiliate marked {$affiliate->status}.");
    }

    public function decideWithdrawal(AffiliateRequest $request, AffiliateWithdrawal $withdrawal): RedirectResponse
    {
        try {
            $this->affiliates->decideWithdrawal($withdrawal, $request->validated('status'), $request->validated('note'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $withdrawal->status === 'approved' ? 'Withdrawal approved and paid.' : 'Withdrawal request rejected.');
    }
}
