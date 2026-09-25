{{--
    One labelled form field with its hint and validation message.

    With no slot it renders a plain input:  <x-admin.field name="sku" label="SKU" :value="$v('sku')" />
    With a slot it wraps your own control, which must use id="f-{name}".
--}}
@props(['name', 'label', 'value' => null, 'type' => 'text', 'hint' => null])
@php
    // "photos[]" and "photos.3" both report errors under "photos"
    $key = rtrim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $invalid = $errors->has($key) || $errors->has($key.'.*');
@endphp
<div class="field">
    <label for="f-{{ $key }}">{{ $label }}</label>
    @if ($slot->isEmpty())
        <input id="f-{{ $key }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}"
               {{ $attributes->class('input') }}
               @if ($invalid) aria-invalid="true" aria-describedby="f-{{ $key }}-error" @endif>
    @else
        {{ $slot }}
    @endif
    @if ($hint)<span class="hint">{{ $hint }}</span>@endif
    @if ($invalid)
        <span class="error" id="f-{{ $key }}-error">{{ $errors->first($key) ?: $errors->first($key.'.*') }}</span>
    @endif
</div>
