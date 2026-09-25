<?php

namespace App\Services;

use App\Enums\RefundStatus;
use App\Models\BusinessSetting;
use App\Models\RefundRequest;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * The admin's side of refunds: deciding requests and the request window.
 *
 * Deciding only records the decision. It deliberately touches neither stock
 * nor payment status:
 *  - Money: orders are cash on delivery, so the cash goes back to the customer
 *    outside WowMart (bKash, bank transfer, cash); there is no gateway to reverse.
 *  - Stock: goods return to stock only when they physically come back, which is
 *    a separate step not built yet.
 */
class RefundService
{
    public const DEFAULT_REQUEST_DAYS = 7;

    /** @throws DomainException when the refund was already decided */
    public function decide(RefundRequest $refund, RefundStatus $to): RefundRequest
    {
        if ($to === RefundStatus::Pending) {
            throw new DomainException('A refund can only be approved or rejected.');
        }

        return DB::transaction(function () use ($refund, $to) {
            // The row lock serialises a double-click or two admins deciding at once;
            // the second one sees the first decision and is refused.
            $refund = RefundRequest::whereKey($refund->id)->lockForUpdate()->firstOrFail();

            if ($refund->refund_status !== RefundStatus::Pending) {
                throw new DomainException('This refund was already '.strtolower($refund->refund_status->label()).'. A decision is final, so there is nothing left to do.');
            }

            $refund->forceFill(['refund_status' => $to])->save();

            return $refund;
        });
    }

    /** Days after delivery a customer may still ask for a refund. */
    public function requestDays(): int
    {
        return (int) BusinessSetting::get('refund_request_time', self::DEFAULT_REQUEST_DAYS);
    }

    public function setRequestDays(int $days): void
    {
        DB::transaction(fn () => BusinessSetting::put('refund_request_time', $days));
    }
}
