{{--
    The blog category fields, shared by the inline "Add" form and the edit page. Expects $category (new or stored).
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $category->name)" required maxlength="60" />
<x-admin.field name="slug" label="Address (slug)" :value="$v('slug', $category->slug)" maxlength="80"
               hint="Leave blank to derive it from the name." />

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($category->status ?? true) : old('status') == 1)>
        Active
    </label>
</div>
