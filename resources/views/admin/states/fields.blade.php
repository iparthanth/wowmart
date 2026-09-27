{{--
    The state fields, shared by the inline "Add" form and the edit page. Expects $state (new or stored) and $countries.
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $countryId = $v('country_id', $state->country_id) ?? ($countries->first()->id ?? null);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $state->name)" required maxlength="60" />

<div class="field">
    <label for="f-country_id">Country</label>
    <select id="f-country_id" name="country_id" class="input" required>
        @foreach ($countries as $c)
            <option value="{{ $c->id }}" @selected((int) $countryId === $c->id)>{{ $c->name }} ({{ $c->code }})</option>
        @endforeach
    </select>
    @if ($errors->has('country_id'))<span class="error">{{ $errors->first('country_id') }}</span>@endif
</div>

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($state->status ?? true) : old('status') == 1)>
        Active
    </label>
</div>
