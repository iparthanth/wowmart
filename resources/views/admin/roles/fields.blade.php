{{--
    The role fields, shared by the inline "Add a role" form and the edit page
    (different pages, so ids never collide). Expects $role (new or stored) and
    $registry (key => {label, patterns}).
--}}
@php
    $held = $role->exists ? $role->permissionKeys() : [];
    $fresh = ! session()->hasOldInput();
@endphp

<x-admin.field name="name" label="Role name" :value="$fresh ? $role->name : old('name')" required maxlength="60" />

<fieldset class="field" style="border:0;padding:0;margin:0 0 16px">
    <legend class="field-label" style="padding:0">Permissions</legend>
    <span class="hint" style="margin-bottom:8px">One checkbox opens that whole section for this role. An unticked box locks it.</span>
    <div class="checks">
        @foreach ($registry as $key => $definition)
            <label class="check">
                <input type="checkbox" name="permissions[]" value="{{ $key }}"
                       @checked($fresh ? in_array($key, $held, true) : in_array($key, old('permissions', []), true))>
                {{ $definition['label'] }}
            </label>
        @endforeach
    </div>
    @error('permissions.*')<span class="error">{{ $message }}</span>@enderror
    @error('permissions')<span class="error">{{ $message }}</span>@enderror
</fieldset>
