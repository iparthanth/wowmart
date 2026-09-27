{{--
    The city fields, shared by the inline "Add" form and the edit page. Expects $city (new or stored) and $states (with country).
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $stateId = $v('state_id', $city->state_id) ?? ($states->first()->id ?? null);
@endphp

<x-admin.field name="name" label="Name" :value="$v('name', $city->name)" required maxlength="60" />

<div class="field">
    <label for="f-state_id">State</label>
    <select id="f-state_id" name="state_id" class="input" required>
        @foreach ($states as $s)
            <option value="{{ $s->id }}" @selected((int) $stateId === $s->id)>{{ $s->name }} - {{ $s->country?->name }}</option>
        @endforeach
    </select>
    @if ($errors->has('state_id'))<span class="error">{{ $errors->first('state_id') }}</span>@endif
</div>

<x-admin.field name="cost" label="Shipping cost" :value="$v('cost', $city->cost)" type="number" step="0.01" min="0"
               hint="What area-wise shipping charges for deliveries to this city. Leave 0 for free." />

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($city->status ?? true) : old('status') == 1)>
        Active
    </label>
</div>
