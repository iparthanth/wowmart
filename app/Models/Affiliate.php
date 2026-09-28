<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A customer enrolled in the affiliate programme, with a referral code and balance. */
#[Fillable(['user_id', 'code', 'status', 'balance'])]
class Affiliate extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected static function booted(): void
    {
        static::creating(function (self $affiliate): void {
            $affiliate->code ??= self::generateCode();
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'AFF-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<AffiliateReferral, $this> */
    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    /** @return HasMany<AffiliateWithdrawal, $this> */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    /** @return HasMany<AffiliateLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(AffiliateLog::class);
    }
}
