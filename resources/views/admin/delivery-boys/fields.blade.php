{{--
    The delivery boy fields, shared by the inline "Add" form and the edit page. Expects $boy (new or stored).
    On edit the password is optional: blank means keep the current one.
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $editing = isset($boy->id);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $boy->name)" required maxlength="120" />
<x-admin.field name="email" label="Email" type="email" :value="$v('email', $boy->email)" required />
<x-admin.field name="phone" label="Phone" :value="$v('phone', $boy->phone)" required maxlength="32" />
<x-admin.field name="password" label="{{ $editing ? 'New password' : 'Password' }}" type="password"
               :hint="$editing ? 'Leave blank to keep the current password.' : 'At least 8 characters.'" />
