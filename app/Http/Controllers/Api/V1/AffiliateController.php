<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AffiliateRequest;
use App\Http\Resources\V1\AffiliateLogResource;
use App\Http\Resources\V1\AffiliateReferralResource;
use App\Http\Resources\V1\AffiliateResource;
use App\Http\Resources\V1\AffiliateWithdrawalResource;
use App\Models\Affiliate;
use App\Models\AffiliateLog;
use App\Models\AffiliateReferral;
use App\Models\AffiliateWithdrawal;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AffiliateController extends Controller
{
    public function __construct(private readonly AffiliateService $affiliates) {}

    // ------------------------------------------------------------- settings

    public function settingsShow(): array
    {
        return ['data' => AffiliateService::settings()];
    }

    public function settingsUpdate(AffiliateRequest $request): array
    {
        $this->affiliates->saveConfigurations($request->validated()); // 409 on conflict

        return ['data' => AffiliateService::settings()];
    }

    public function registrationShow(): array
    {
        $settings = AffiliateService::settings();

        return ['data' => [
            'affiliate_registration_enabled' => $settings['affiliate_registration_enabled'],
            'fields' => json_decode($settings['affiliate_registration_fields'], true) ?? [],
        ]];
    }

    public function registrationUpdate(AffiliateRequest $request): array
    {
        $this->affiliates->saveRegistrationForm($request->validated());

        return $this->registrationShow();
    }

    // -------------------------------------------------------- affiliate users

    public function usersIndex(Request $request): AnonymousResourceCollection
    {
        return AffiliateResource::collection(
            Affiliate::query()
                ->with('user')
                ->withCount('referrals')
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function usersShow(Affiliate $affiliate): AffiliateResource
    {
        return new AffiliateResource($affiliate->load('user'));
    }

    public function usersUpdate(AffiliateRequest $request, Affiliate $affiliate): AffiliateResource
    {
        if ($request->validated('status') !== null) {
            $this->affiliates->decideAffiliate($affiliate, $request->validated('status')); // 409 when already decided
        }

        return new AffiliateResource($affiliate->load('user'));
    }

    public function usersDestroy(Affiliate $affiliate): Response
    {
        $affiliate->delete();

        return response()->noContent();
    }

    // -------------------------------------------------------- referral users

    public function referralsIndex(Request $request): AnonymousResourceCollection
    {
        return AffiliateReferralResource::collection(
            AffiliateReferral::query()
                ->with(['referredUser', 'affiliate.user'])
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    // ----------------------------------------------------------- withdrawals

    public function withdrawalsIndex(Request $request): AnonymousResourceCollection
    {
        return AffiliateWithdrawalResource::collection(
            AffiliateWithdrawal::query()
                ->with('affiliate.user')
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function withdrawalsShow(AffiliateWithdrawal $withdrawal): AffiliateWithdrawalResource
    {
        return new AffiliateWithdrawalResource($withdrawal->load('affiliate.user'));
    }

    public function withdrawalsUpdate(AffiliateRequest $request, AffiliateWithdrawal $withdrawal): AffiliateWithdrawalResource
    {
        if ($request->validated('status') !== null) {
            $this->affiliates->decideWithdrawal($withdrawal, $request->validated('status'), $request->validated('note')); // 409 when already decided
        } elseif ($request->validated('note') !== null) {
            $withdrawal->update(['note' => $request->validated('note')]);
        }

        return new AffiliateWithdrawalResource($withdrawal->load('affiliate.user'));
    }

    public function withdrawalsDestroy(AffiliateWithdrawal $withdrawal): Response
    {
        $withdrawal->delete();

        return response()->noContent();
    }

    // ------------------------------------------------------------------ logs

    public function logsIndex(Request $request): AnonymousResourceCollection
    {
        return AffiliateLogResource::collection(
            AffiliateLog::query()
                ->with(['affiliate.user', 'referredUser', 'order'])
                ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function logsShow(AffiliateLog $log): AffiliateLogResource
    {
        return new AffiliateLogResource($log->load(['affiliate.user', 'referredUser', 'order']));
    }
}
