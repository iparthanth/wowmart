{{--
    The blog post fields, shared by the inline "Add" form and the edit page. Expects $post (new or stored) and $categories.
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $categoryId = $v('blog_category_id', $post->blog_category_id) ?? ($categories->first()->id ?? null);
@endphp

<x-admin.field name="title" label="Title" :value="$v('title', $post->title)" required maxlength="160" />

<div class="split" style="gap:12px">
    <div class="field">
        <label for="f-blog_category_id">Category</label>
        <select id="f-blog_category_id" name="blog_category_id" class="input" required>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected((int) $categoryId === $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @if ($errors->has('blog_category_id'))<span class="error">{{ $errors->first('blog_category_id') }}</span>@endif
    </div>
    <div class="field">
        <label class="check" style="margin-top:26px">
            <input type="hidden" name="status" value="0">
            <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($post->status ?? true) : old('status') == 1)>
            Active
        </label>
    </div>
</div>

<x-admin.field name="slug" label="Address (slug)" :value="$v('slug', $post->slug)" maxlength="180"
               hint="The post lives at /blog/... - leave blank to derive it from the title." />
<x-admin.field name="short_description" label="Short description" :value="$v('short_description', $post->short_description)" maxlength="300"
               hint="Shown on blog lists." />
<div class="field">
    <span class="field-label">Banner image</span>
    <x-admin.media-picker name="banner" :images="$images" :selected="$v('banner', $post->banner)" />
    <span class="hint">Shown at the top of the post. Upload new pictures on Uploaded Files first.</span>
    @error('banner')<span class="error">{{ $message }}</span>@enderror
</div>

<div class="field">
    <label for="f-description">Body</label>
    <textarea id="f-description" name="description" rows="12" class="input">{{ $v('description', $post->description) }}</textarea>
    @if ($errors->has('description'))<span class="error">{{ $errors->first('description') }}</span>@endif
</div>

<div class="split" style="gap:12px">
    <x-admin.field name="meta_title" label="Meta title" :value="$v('meta_title', $post->meta_title)" maxlength="180" />
    <x-admin.field name="meta_description" label="Meta description" :value="$v('meta_description', $post->meta_description)" maxlength="300" />
</div>
