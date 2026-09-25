{{--
    The colour fields, shared by the inline "Add a colour" form and the edit page. Expects $color (new or stored).
--}}
@php
    // After a failed save show what was typed; otherwise show what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $code = $v('code', $color->code);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $color->name)" required maxlength="60" />

{{-- A colour input only accepts #rrggbb; anything else it was given would render as black. --}}
<x-admin.field name="code" label="Colour" type="color" :value="is_string($code) && preg_match('/^#[0-9A-Fa-f]{6}$/', $code) ? strtolower($code) : '#000000'"
               required hint="Saved as a hex code, like #1E5AA8." />
