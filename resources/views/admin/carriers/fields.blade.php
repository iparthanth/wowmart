{{--
    The carrier fields, shared by the inline "Add" form and the edit page. Expects $carrier (new or stored).
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $carrier->name)" required maxlength="60" />
<x-admin.field name="transit_time" label="Transit time" :value="$v('transit_time', $carrier->transit_time)" maxlength="60"
               hint="Shown to customers, like 2-4 days." />

<div class="field">
    <label class="check">
        <input type="hidden" name="free_shipping" value="0">
        <input id="f-free_shipping" type="checkbox" name="free_shipping" value="1" @checked($fresh ? (bool) ($carrier->free_shipping ?? false) : old('free_shipping') == 1)>
        Free shipping - the carrier ignores billing ranges
    </label>
</div>

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($carrier->status ?? true) : old('status') == 1)>
        Active
    </label>
</div>
