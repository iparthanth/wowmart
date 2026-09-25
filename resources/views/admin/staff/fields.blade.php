{{--
    The staff fields, shared by the inline "Add a member" form and the edit page
    (different pages, so ids never collide). Expects $member (new or stored) and
    $roles.
--}}
@php
    $editing = $member->exists;
    // After a failed save show what was typed; otherwise what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $member->name)" required maxlength="80" />

<x-admin.field name="email" label="Email" type="email" :value="$v('email', $member->email)" required maxlength="255"
               hint="Used to sign in. Must be unique across the whole marketplace." />

<x-admin.field name="password" label="{{ $editing ? 'New password' : 'Password' }}" type="password"
               :required="! $editing" minlength="8" autocomplete="new-password"
               :hint="$editing ? 'Leave blank to keep the current password.' : null" />

<div class="checks">
    <label class="check">
        <input type="checkbox" name="is_admin" value="1" @checked($v('is_admin', $editing ? $member->isAdmin() : false))>
        Administrator - full access, holds no role
    </label>
</div>

<x-admin.field name="role_id" label="Role">
    <select id="f-role_id" name="role_id" class="select">
        <option value="">Choose a role</option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}" @selected($v('role_id', $member->role_id) == $role->id)>{{ $role->name }}</option>
        @endforeach
    </select>
    <span class="hint">Required unless the member is an administrator.</span>
</x-admin.field>
