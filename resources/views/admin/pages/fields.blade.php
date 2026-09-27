{{--
    The page fields, shared by the inline "Add" form and the edit page. Expects $page (new or stored).
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<div class="split" style="gap:12px">
    <div class="field">
        <label for="f-type">Type</label>
        <select id="f-type" name="type" class="input" required>
            @foreach (App\Models\Page::TYPES as $t)
                <option value="{{ $t }}" @selected(($v('type', $page->type) ?? 'custom') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label class="check" style="margin-top:26px">
            <input type="hidden" name="status" value="0">
            <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($page->status ?? true) : old('status') == 1)>
            Active
        </label>
    </div>
</div>

<x-admin.field name="title" label="Title" :value="$v('title', $page->title)" required maxlength="120" />
<x-admin.field name="slug" label="Address (slug)" :value="$v('slug', $page->slug)" maxlength="140"
               hint="The page lives at /page/... - leave blank to derive it from the title." />

<div class="field">
    <label for="f-content">Content</label>
    <textarea id="f-content" name="content" rows="12" class="input">{{ $v('content', $page->content) }}</textarea>
    @if ($errors->has('content'))<span class="error">{{ $errors->first('content') }}</span>@endif
</div>

<div class="split" style="gap:12px">
    <x-admin.field name="meta_title" label="Meta title" :value="$v('meta_title', $page->meta_title)" maxlength="180" />
    <x-admin.field name="meta_description" label="Meta description" :value="$v('meta_description', $page->meta_description)" maxlength="300" />
</div>
