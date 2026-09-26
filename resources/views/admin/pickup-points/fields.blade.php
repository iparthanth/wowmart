{{--
    The pickup point fields, shared by the inline "Add" form and the edit page. Expects $point (new or stored).
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $addr = fn (string $key, $stored = null) => $v('address.'.$key, $point->address[$key] ?? null);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $point->name)" required maxlength="80"
               hint="What customers see at checkout, like GEC Circle branch." />
<x-admin.field name="phone" label="Phone" :value="$v('phone', $point->phone)" required maxlength="20" />

<div class="field">
    <label for="f-address-address">Street address</label>
    <textarea id="f-address-address" name="address[address]" class="input" rows="2" required>{{ $addr('address', $point->address['address'] ?? null) }}</textarea>
    @if ($errors->has('address.address'))<span class="error">{{ $errors->first('address.address') }}</span>@endif
</div>

<div class="split" style="gap:12px">
    <x-admin.field name="address[city]" label="City" :value="$addr('city', $point->address['city'] ?? null)" required maxlength="60" />
    <x-admin.field name="address[country]" label="Country" :value="$addr('country', $point->address['country'] ?? 'Bangladesh')" required maxlength="60" />
</div>

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($point->status ?? true) : old('status') == 1)>
        Active - offered to customers at checkout
    </label>
</div>
