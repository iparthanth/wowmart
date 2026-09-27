{{--
    The zone fields, shared by the inline "Add" form and the edit page. Expects $zone (new or stored) and $countries.
--}}
@php
    $fresh = ! session()->hasOldInput();
    $selected = $fresh ? old('countries', $zone->countries->pluck('id')->all()) : old('countries', []);
@endphp

<x-admin.field name="name" label="Name" :value="$fresh ? old('name', $zone->name) : old('name')" required maxlength="60"
               hint="Zones are carrier billing groups: a range prices each zone once." />

<div class="field">
    <label for="f-countries">Countries in this zone</label>
    <select id="f-countries" name="countries[]" class="input" multiple size="8" required>
        @foreach ($countries as $c)
            <option value="{{ $c->id }}" @selected(in_array($c->id, $selected))>{{ $c->name }} ({{ $c->code }})</option>
        @endforeach
    </select>
    <span class="hint">Hold Ctrl (or Cmd) to pick several.</span>
    @if ($errors->has('countries') || $errors->has('countries.*'))<span class="error">{{ $errors->first('countries') ?: $errors->first('countries.*') }}</span>@endif
</div>

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($zone->status ?? true) : old('status') == 1)>
        Active
    </label>
</div>
