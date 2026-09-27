{{--
    The alert fields, shared by the inline "Add" form and the edit page. Expects $alert (new or stored) and $styles.
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="title" label="Title" :value="$v('title', $alert->title)" required maxlength="160" />
<div class="field">
    <label for="f-description">Description</label>
    <textarea id="f-description" name="description" rows="3" class="input">{{ $v('description', $alert->description) }}</textarea>
</div>

<div class="split" style="gap:12px">
    <div class="field">
        <label for="f-style">Style</label>
        <select id="f-style" name="style" class="input" required>
            @foreach ($styles as $s)
                <option value="{{ $s }}" @selected(($v('style', $alert->style) ?? 'info') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label class="check" style="margin-top:26px">
            <input type="hidden" name="status" value="0">
            <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($alert->status ?? true) : old('status') == 1)>
            Active
        </label>
    </div>
</div>
