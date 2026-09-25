{{-- The category form, shared by create and edit. --}}
@php
    // After a failed save show what was typed; otherwise show what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<form method="POST" action="{{ $action }}" class="form-layout">
    @csrf
    @if ($category->exists) @method('PUT') @endif

    <div class="form-main">
        @if ($errors->any())
            <div class="flash flash-bad" role="alert">
                {{ $errors->count() === 1 ? 'One field needs' : $errors->count().' fields need' }} fixing before this can be saved.
            </div>
        @endif

        <section class="card form-section" aria-labelledby="general-h">
            <div class="card-head"><h2 id="general-h">Category</h2></div>
            <div class="card-body">
                <x-admin.field name="name" label="Name" :value="$v('name', $category->name)" required maxlength="100" />

                <div class="grid-2">
                    <x-admin.field name="parent_id" label="Parent category" hint="Leave empty for a top-level category.">
                        <select id="f-parent_id" name="parent_id" class="select" @error('parent_id') aria-invalid="true" @enderror>
                            <option value="">None (top level)</option>
                            @foreach ($parents as $c)
                                <option value="{{ $c->id }}" @selected((int) $v('parent_id', $category->parent_id) === $c->id)>{{ str_repeat('— ', $c->level) }}{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>

                    <x-admin.field name="order_level" label="Order level" type="number" :value="$v('order_level', $category->order_level)"
                                   min="0" max="9999" step="1" hint="Lower numbers show first." />
                </div>

                <div class="checks">
                    <label class="check"><input type="checkbox" name="featured" value="1" @checked($v('featured', $category->featured))> Featured on the home page</label>
                    <label class="check"><input type="checkbox" name="digital" value="1" @checked($v('digital', $category->digital))> Digital products</label>
                </div>
            </div>
        </section>

        <section class="card form-section" aria-labelledby="images-h">
            <div class="card-head"><h2 id="images-h">Images</h2></div>
            <div class="card-body">
                <x-admin.field name="icon" label="Icon" hint="Shown next to the name in menus.">
                    <x-admin.media-picker name="icon" :images="$images" :selected="$v('icon', $category->icon)" />
                </x-admin.field>
                <x-admin.field name="banner" label="Banner">
                    <x-admin.media-picker name="banner" :images="$images" :selected="$v('banner', $category->banner)" />
                </x-admin.field>
                <x-admin.field name="cover_image" label="Cover image">
                    <x-admin.media-picker name="cover_image" :images="$images" :selected="$v('cover_image', $category->cover_image)" />
                </x-admin.field>
            </div>
        </section>

        <section class="card form-section" aria-labelledby="seo-h">
            <div class="card-head"><h2 id="seo-h">SEO</h2></div>
            <div class="card-body">
                <x-admin.field name="meta_title" label="Meta title" :value="$v('meta_title', $category->meta_title)" maxlength="255" />
                <x-admin.field name="meta_description" label="Meta description">
                    <textarea id="f-meta_description" name="meta_description" class="textarea" rows="3" maxlength="1000">{{ $v('meta_description', $category->meta_description) }}</textarea>
                </x-admin.field>
            </div>
        </section>
    </div>

    <aside class="form-aside">
        <div class="card">
            <div class="card-body aside-actions">
                <button type="submit" class="btn btn-primary btn-block">{{ $category->exists ? 'Save changes' : 'Create category' }}</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-block">Cancel</a>
            </div>
            @if ($category->exists)
                <dl class="facts">
                    <div><dt>Level</dt><dd class="num">{{ $category->level }}</dd></div>
                    <div><dt>Slug</dt><dd class="mono">{{ $category->slug }}</dd></div>
                </dl>
            @endif
        </div>
    </aside>
</form>
