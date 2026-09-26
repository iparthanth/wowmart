{{--
    The tax fields, shared by the inline "Add a tax" form and the edit page. Expects $tax (new or stored).
--}}
@php
    $fresh = ! session()->hasOldInput();
@endphp

<x-admin.field name="name" label="Name" :value="$fresh ? old('name', $tax->name ?? '') : old('name')" required maxlength="60"
               hint="Like VAT or GST - shown on invoices." />

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($tax->status ?? true) : old('status') == 1)>
        Active
    </label>
</div>
