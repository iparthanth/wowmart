{{--
    The currency fields, shared by the inline "Add a currency" form and the edit page. Expects $currency (new or stored).
--}}
@php
    // After a failed save show what was typed; otherwise show what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $currency->name)" required maxlength="60" />
<x-admin.field name="symbol" label="Symbol" :value="$v('symbol', $currency->symbol)" required maxlength="8" hint="Like $ or €." />
<x-admin.field name="code" label="ISO code" :value="$v('code', $currency->code)" required maxlength="3"
               hint="Exactly three letters, like USD." />
<x-admin.field name="exchange_rate" label="Exchange rate" type="number" step="0.000001" min="0.000001"
               :value="$v('exchange_rate', $currency->exchange_rate ?? '1')" required
               hint="How many units of this currency equal one unit of the system currency." />

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($currency->status ?? true) : old('status') == 1)>
        Active
    </label>
</div>
