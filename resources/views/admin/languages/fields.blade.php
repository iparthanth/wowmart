{{--
    The language fields, shared by the inline "Add" form and the edit page. Expects $language (new or stored).
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $language->name)" required maxlength="60" hint="Shown in the language switcher, e.g. English or বাংলা." />
<x-admin.field name="code" label="Code" :value="$v('code', $language->code)" required maxlength="12" hint="A short code like en, bn or ar. Used in URLs and translation files." />
<label class="check">
    <input type="hidden" name="rtl" value="0">
    <input type="checkbox" name="rtl" value="1" @checked($fresh ? (bool) ($language->rtl ?? false) : old('rtl') == 1)>
    Right-to-left (Arabic, Hebrew, Urdu...)
</label>
<label class="check">
    <input type="hidden" name="status" value="0">
    <input type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($language->status ?? true) : old('status') == 1)>
    Enabled
</label>
