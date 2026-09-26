<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CurrencyRequest;
use App\Models\BusinessSetting;
use App\Models\Currency;
use App\Services\CurrencyService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function __construct(private readonly CurrencyService $currencies) {}

    /** The list, with the "Add a currency" form beside it. */
    public function index(): View
    {
        return view('admin.currencies.index', [
            'currencies' => Currency::orderBy('name')->paginate(20),
            'newCurrency' => new Currency,
            'systemId' => (int) BusinessSetting::get('system_currency', 0),
        ]);
    }

    public function store(CurrencyRequest $request): RedirectResponse
    {
        $currency = $this->currencies->create($request->validated());

        return redirect()->route('admin.currencies.index')->with('status', "Currency \"{$currency->name}\" added.");
    }

    public function edit(Currency $currency): View
    {
        return view('admin.currencies.edit', [
            'currency' => $currency,
            'systemId' => (int) BusinessSetting::get('system_currency', 0),
        ]);
    }

    public function update(CurrencyRequest $request, Currency $currency): RedirectResponse
    {
        $this->currencies->update($currency, $request->validated());

        return redirect()->route('admin.currencies.edit', $currency)->with('status', 'Changes saved.');
    }

    public function destroy(Currency $currency): RedirectResponse
    {
        try {
            $this->currencies->delete($currency);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.currencies.index')->with('status', "Currency \"{$currency->name}\" deleted.");
    }

    public function makeSystem(Currency $currency): RedirectResponse
    {
        try {
            $this->currencies->makeSystem($currency);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.currencies.index')->with('status', "\"{$currency->name}\" is now the system currency.");
    }
}
