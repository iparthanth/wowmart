{{--
    The brand fields, shared by the inline "Add a brand" form and the edit page.
    Expects $brand (new or stored) and $images (the image library).
--}}
@php
    // After a failed save show what was typed; otherwise show what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $brand->name)" required maxlength="100" />

<x-admin.field name="logo" label="Logo" hint="Square images look best in the shop's brand strip.">
    <x-admin.media-picker name="logo" :images="$images" :selected="$v('logo', $brand->logo)" />
</x-admin.field>

<div class="checks">
    <label class="check"><input type="checkbox" name="top" value="1" @checked($v('top', $brand->top))> Show in top brands</label>
</div>

<x-admin.field name="meta_title" label="Meta title" :value="$v('meta_title', $brand->meta_title)" maxlength="255"
               hint="Shown in search results. Leave empty to use the brand name." />

<x-admin.field name="meta_description" label="Meta description">
    <textarea id="f-meta_description" name="meta_description" class="textarea" rows="3" maxlength="1000">{{ $v('meta_description', $brand->meta_description) }}</textarea>
</x-admin.field>
