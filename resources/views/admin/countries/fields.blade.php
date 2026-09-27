{{--
    The country fields, shared by the inline "Add" form and the edit page. Expects $country (new or stored).
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $country->name)" required maxlength="60" />
<x-admin.field name="code" label="ISO code" :value="$v('code', $country->code)" required maxlength="3"
               hint="Two or three letters, like BD or USA." style="text-transform:uppercase" />

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($country->status ?? true) : old('status') == 1)>
        Active - shippable everywhere the storefront asks
    </label>
</div>
