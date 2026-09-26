<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Currency;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every write to a currency. Input is validated already (CurrencyRequest). */
class CurrencyService
{
    public function create(array $data): Currency
    {
        return DB::transaction(fn () => Currency::create($data));
    }

    public function update(Currency $currency, array $data): Currency
    {
        return DB::transaction(fn () => tap($currency)->update($data));
    }

    /** @throws DomainException while the currency is the panel's system currency */
    public function delete(Currency $currency): void
    {
        if ((int) BusinessSetting::get('system_currency', 0) === $currency->id) {
            throw new DomainException('This is the system currency. Choose another currency as system currency first.');
        }

        $currency->delete();
    }

    /** @throws DomainException when the currency is unknown, disabled, or already active */
    public function makeSystem(Currency $currency): void
    {
        if (! $currency->status) {
            throw new DomainException('Enable the currency before making it the system currency.');
        }

        BusinessSetting::put('system_currency', $currency->id);
    }
}
