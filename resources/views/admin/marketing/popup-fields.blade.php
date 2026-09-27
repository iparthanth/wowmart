{{--
    The pop-up fields, shared by the inline "Add" form and the edit page. Expects $popup (new or stored) and $pages.
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<x-admin.field name="title" label="Title" :value="$v('title', $popup->title)" required maxlength="120" />
<div class="field">
    <label for="f-description">Description</label>
    <textarea id="f-description" name="description" rows="4" class="input">{{ $v('description', $popup->description) }}</textarea>
</div>
<x-admin.field name="banner" label="Banner image (upload URL)" :value="$v('banner', $popup->banner)" maxlength="255" />
<x-admin.field name="link" label="Link" :value="$v('link', $popup->link)" maxlength="255" hint="Where the pop-up sends the visitor." />

<div class="split" style="gap:12px">
    <div class="field">
        <label for="f-page">Shows on</label>
        <select id="f-page" name="page" class="input" required>
            @foreach ($pages as $p)
                <option value="{{ $p }}" @selected(($v('page', $popup->page) ?? 'all') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label class="check" style="margin-top:26px">
            <input type="hidden" name="once_per_session" value="0">
            <input id="f-once" type="checkbox" name="once_per_session" value="1" @checked($fresh ? (bool) ($popup->once_per_session ?? true) : old('once_per_session') == 1)>
            Once per session
        </label>
    </div>
</div>

<div class="field">
    <label class="check">
        <input type="hidden" name="status" value="0">
        <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($popup->status ?? true) : old('status') == 1)>
        Active
    </label>
</div>
