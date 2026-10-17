{{--
    The product form, shared by create and edit.

    Same seven groups as the reference panel, but stacked on one page with a
    section index instead of tabs: after a failed save every error is visible
    at once, and nothing needs JavaScript.
--}}
@use('App\Enums\DiscountType')
@use('App\Enums\ShippingType')
@php
    // After a failed save show what was typed; otherwise show what is stored.
    // (old() alone can't tell "unticked" from "never submitted".)
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);

    $sections = [
        'general' => ['General', ['name', 'category_id', 'category_ids', 'category_ids.*', 'brand_id', 'unit', 'weight', 'min_qty', 'tags', 'barcode', 'quick_overview', 'description', 'refund_note_id']],
        'media' => ['Files & media', ['photos', 'photos.*', 'thumbnail_img', 'video_provider', 'video_link', 'pdf']],
        'price' => ['Price & stock', ['unit_price', 'discount', 'discount_type', 'discount_start_date', 'discount_end_date', 'current_stock', 'sku', 'low_stock_quantity', 'stock_visibility_state', 'external_link', 'external_link_btn']],
        'seo' => ['SEO', ['meta_title', 'meta_description', 'meta_img']],
        'shipping' => ['Shipping', ['shipping_type', 'shipping_cost', 'est_shipping_days']],
        'warranty' => ['Warranty', ['warranty_id', 'warranty_note_id']],
        'related' => ['Frequently bought', ['fq_bought_product_category_id']],
    ];

    $listed = array_map('intval', (array) $v('category_ids', $product->categories->pluck('id')->all()));
    $visibility = [
        'quantity' => 'Show the number in stock',
        'text' => 'Show "In stock" without a number',
        'hide' => 'Hide stock from customers',
    ];
    $date = fn ($d) => $d?->format('Y-m-d\TH:i');
@endphp

<form method="POST" action="{{ $action }}" class="form-layout">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <div class="form-main">
        @if ($errors->any())
            <div class="flash flash-bad" role="alert">
                {{ $errors->count() === 1 ? 'One field needs' : $errors->count().' fields need' }} fixing before this can be saved. Sections with problems are marked in the index.
            </div>
        @endif

        {{-- General ------------------------------------------------------ --}}
        <section class="card form-section" id="general" aria-labelledby="general-h">
            <div class="card-head"><h2 id="general-h">General</h2></div>
            <div class="card-body">
                <x-admin.field name="name" label="Product name" :value="$v('name', $product->name)" required maxlength="200" />

                <div class="grid-2">
                    <x-admin.field name="category_id" label="Main category" hint="Where the product sits in the shop's navigation.">
                        <select id="f-category_id" name="category_id" class="select" required @error('category_id') aria-invalid="true" @enderror>
                            <option value="">Choose a category</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" @selected((int) $v('category_id', $product->category_id) === $c->id)>{{ str_repeat('— ', $c->level) }}{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>

                    <x-admin.field name="brand_id" label="Brand">
                        <select id="f-brand_id" name="brand_id" class="select">
                            <option value="">No brand</option>
                            @foreach ($brands as $b)
                                <option value="{{ $b->id }}" @selected((int) $v('brand_id', $product->brand_id) === $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                </div>

                <fieldset class="field">
                    <legend class="field-label">Also list in</legend>
                    <div class="check-list">
                        @foreach ($categories as $c)
                            <label class="check" style="padding-left: {{ $c->level * 18 }}px">
                                <input type="checkbox" name="category_ids[]" value="{{ $c->id }}" @checked(in_array($c->id, $listed, true))>
                                {{ $c->name }}
                            </label>
                        @endforeach
                    </div>
                    <span class="hint">The main category is always included.</span>
                    @error('category_ids.*')<span class="error">{{ $message }}</span>@enderror
                </fieldset>

                <div class="grid-3">
                    <x-admin.field name="unit" label="Unit" :value="$v('unit', $product->unit)" placeholder="pc, kg, roll" maxlength="32" />
                    <x-admin.field name="weight" label="Weight (kg)" type="number" :value="$v('weight', $product->weight)" step="0.001" min="0" />
                    <x-admin.field name="min_qty" label="Minimum order" type="number" :value="$v('min_qty', $product->min_qty)" min="1" required />
                </div>

                <div class="grid-2">
                    <x-admin.field name="tags" label="Search tags" :value="$v('tags', $product->tags)" hint="Separate with commas." maxlength="1000" />
                    <x-admin.field name="barcode" label="Barcode" :value="$v('barcode', $product->barcode)" class="mono" maxlength="64" />
                </div>

                <x-admin.field name="quick_overview" label="Quick overview" hint="Two or three lines shown beside the price.">
                    <textarea id="f-quick_overview" name="quick_overview" class="textarea" rows="3" maxlength="2000">{{ $v('quick_overview', $product->quick_overview) }}</textarea>
                </x-admin.field>

                <x-admin.field name="description" label="Description">
                    <textarea id="f-description" name="description" class="textarea" rows="10">{{ $v('description', $product->description) }}</textarea>
                </x-admin.field>

                <div class="checks">
                    <label class="check"><input type="checkbox" name="featured" value="1" @checked($v('featured', $product->featured))> Featured on the home page</label>
                    <label class="check"><input type="checkbox" name="todays_deal" value="1" @checked($v('todays_deal', $product->todays_deal))> Today's deal</label>
                    <label class="check"><input type="checkbox" name="refundable" value="1" @checked($v('refundable', $product->refundable))> Refundable</label>
                </div>

                <x-admin.field name="refund_note_id" label="Refund note" hint="Shown to customers when the product is refundable.">
                    <select id="f-refund_note_id" name="refund_note_id" class="select">
                        <option value="">No note</option>
                        @foreach ($notes['refund'] ?? [] as $n)
                            <option value="{{ $n->id }}" @selected((int) $v('refund_note_id', $product->refund_note_id) === $n->id)>{{ Str::limit($n->description, 80) }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>
        </section>

        {{-- Files & media ----------------------------------------------- --}}
        <section class="card form-section" id="media" aria-labelledby="media-h">
            <div class="card-head"><h2 id="media-h">Files &amp; media</h2></div>
            <div class="card-body">
                <div class="field">
                    <span class="field-label">Gallery</span>
                    <x-admin.media-picker name="photos" multiple :images="$images" :selected="$v('photos', $product->gallery->pluck('id')->all())" />
                    <span class="hint">Shown in the order chosen. New files go on Uploaded Files first.</span>
                    @if ($errors->has('photos') || $errors->has('photos.*'))
                        <span class="error">{{ $errors->first('photos') ?: $errors->first('photos.*') }}</span>
                    @endif
                </div>

                <div class="field">
                    <span class="field-label">Thumbnail</span>
                    <x-admin.media-picker name="thumbnail_img" :images="$images" :selected="$v('thumbnail_img', $product->thumbnail_img)" />
                    <span class="hint">Left empty, the first gallery image is used.</span>
                    @error('thumbnail_img')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="grid-2">
                    <x-admin.field name="video_provider" label="Video host">
                        <select id="f-video_provider" name="video_provider" class="select">
                            <option value="">No video</option>
                            @foreach (['youtube' => 'YouTube', 'vimeo' => 'Vimeo'] as $key => $label)
                                <option value="{{ $key }}" @selected($v('video_provider', $product->video_provider) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                    <x-admin.field name="video_link" label="Video link" type="url" :value="$v('video_link', $product->video_link)" placeholder="https://" />
                </div>

                <x-admin.field name="pdf" label="Specification PDF">
                    <select id="f-pdf" name="pdf" class="select">
                        <option value="">No PDF</option>
                        @foreach ($documents as $doc)
                            <option value="{{ $doc->id }}" @selected((int) $v('pdf', $product->pdf) === $doc->id)>{{ $doc->file_original_name }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>
        </section>

        {{-- Price & stock ----------------------------------------------- --}}
        <section class="card form-section" id="price" aria-labelledby="price-h">
            <div class="card-head"><h2 id="price-h">Price &amp; stock</h2></div>
            <div class="card-body">
                <div class="grid-3">
                    <x-admin.field name="unit_price" label="Price (৳)" type="number" :value="$v('unit_price', $product->unit_price)" step="0.01" min="0" required />
                    <x-admin.field name="discount" label="Discount" type="number" :value="$v('discount', $product->discount)" step="0.01" min="0" />
                    <x-admin.field name="discount_type" label="Discount is">
                        <select id="f-discount_type" name="discount_type" class="select">
                            @foreach (DiscountType::cases() as $t)
                                <option value="{{ $t->value }}" @selected($v('discount_type', $product->discount_type?->value) === $t->value)>{{ $t === DiscountType::Percent ? 'A percentage' : 'A fixed amount (৳)' }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                </div>

                <div class="grid-2">
                    <x-admin.field name="discount_start_date" label="Discount starts" type="datetime-local" :value="$v('discount_start_date', $date($product->discount_start_date))" hint="Empty: starts straight away." />
                    <x-admin.field name="discount_end_date" label="Discount ends" type="datetime-local" :value="$v('discount_end_date', $date($product->discount_end_date))" hint="Empty: runs until removed." />
                </div>

                <div class="grid-3">
                    <x-admin.field name="current_stock" label="In stock" type="number" :value="$v('current_stock', $product->current_stock)" min="0" required />
                    <x-admin.field name="low_stock_quantity" label="Warn when stock reaches" type="number" :value="$v('low_stock_quantity', $product->low_stock_quantity)" min="0" required />
                    <x-admin.field name="sku" label="SKU" :value="$v('sku', $product->sku)" class="mono" maxlength="64" />
                </div>

                <fieldset class="field">
                    <legend class="field-label">Stock shown to customers</legend>
                    <div class="checks">
                        @foreach ($visibility as $key => $label)
                            <label class="check">
                                <input type="radio" name="stock_visibility_state" value="{{ $key }}" @checked($v('stock_visibility_state', $product->stock_visibility_state?->value) === $key)>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('stock_visibility_state')<span class="error">{{ $message }}</span>@enderror
                </fieldset>

                <div class="grid-2">
                    <x-admin.field name="external_link" label="External link" type="url" :value="$v('external_link', $product->external_link)" hint="Sends buyers to another site instead of the cart." placeholder="https://" />
                    <x-admin.field name="external_link_btn" label="Button text" :value="$v('external_link_btn', $product->external_link_btn)" placeholder="Buy on the maker's site" maxlength="60" />
                </div>
            </div>
        </section>

        {{-- SEO --------------------------------------------------------- --}}
        <section class="card form-section" id="seo" aria-labelledby="seo-h">
            <div class="card-head"><h2 id="seo-h">SEO</h2></div>
            <div class="card-body">
                <x-admin.field name="meta_title" label="Page title" :value="$v('meta_title', $product->meta_title)" hint="Empty: the product name is used." maxlength="255" />
                <x-admin.field name="meta_description" label="Search description">
                    <textarea id="f-meta_description" name="meta_description" class="textarea" rows="3" maxlength="1000">{{ $v('meta_description', $product->meta_description) }}</textarea>
                </x-admin.field>
                <div class="field">
                    <span class="field-label">Share image</span>
                    <x-admin.media-picker name="meta_img" :images="$images" :selected="$v('meta_img', $product->meta_img)" />
                    @error('meta_img')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </section>

        {{-- Shipping ---------------------------------------------------- --}}
        <section class="card form-section" id="shipping" aria-labelledby="shipping-h">
            <div class="card-head"><h2 id="shipping-h">Shipping</h2></div>
            <div class="card-body">
                <fieldset class="field">
                    <legend class="field-label">Shipping charge</legend>
                    <div class="checks">
                        @foreach (ShippingType::cases() as $t)
                            <label class="check">
                                <input type="radio" name="shipping_type" value="{{ $t->value }}" @checked($v('shipping_type', $product->shipping_type?->value) === $t->value)>
                                {{ $t === ShippingType::Free ? 'Free shipping' : 'Flat rate' }}
                            </label>
                        @endforeach
                    </div>
                    @error('shipping_type')<span class="error">{{ $message }}</span>@enderror
                </fieldset>

                <div class="grid-2">
                    <x-admin.field name="shipping_cost" label="Flat rate (৳)" type="number" :value="$v('shipping_cost', $product->shipping_cost)" step="0.01" min="0" hint="Only used with a flat rate." />
                    <x-admin.field name="est_shipping_days" label="Delivered within (days)" type="number" :value="$v('est_shipping_days', $product->est_shipping_days)" min="1" max="365" />
                </div>

                <div class="checks">
                    <label class="check"><input type="checkbox" name="is_quantity_multiplied" value="1" @checked($v('is_quantity_multiplied', $product->is_quantity_multiplied))> Charge the flat rate per item</label>
                    <label class="check"><input type="checkbox" name="cash_on_delivery" value="1" @checked($v('cash_on_delivery', $product->cash_on_delivery))> Cash on delivery available</label>
                </div>
            </div>
        </section>

        {{-- Warranty ---------------------------------------------------- --}}
        <section class="card form-section" id="warranty" aria-labelledby="warranty-h">
            <div class="card-head"><h2 id="warranty-h">Warranty</h2></div>
            <div class="card-body">
                <div class="checks" style="margin-bottom:16px">
                    <label class="check"><input type="checkbox" name="has_warranty" value="1" @checked($v('has_warranty', $product->has_warranty))> This product has a warranty</label>
                </div>
                <div class="grid-2">
                    <x-admin.field name="warranty_id" label="Warranty period">
                        <select id="f-warranty_id" name="warranty_id" class="select" @error('warranty_id') aria-invalid="true" @enderror>
                            <option value="">Choose a period</option>
                            @foreach ($warranties as $w)
                                <option value="{{ $w->id }}" @selected((int) $v('warranty_id', $product->warranty_id) === $w->id)>{{ $w->text }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                    <x-admin.field name="warranty_note_id" label="Warranty note">
                        <select id="f-warranty_note_id" name="warranty_note_id" class="select">
                            <option value="">No note</option>
                            @foreach ($notes['warranty'] ?? [] as $n)
                                <option value="{{ $n->id }}" @selected((int) $v('warranty_note_id', $product->warranty_note_id) === $n->id)>{{ Str::limit($n->description, 80) }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                </div>
            </div>
        </section>

        {{-- Frequently bought ------------------------------------------- --}}
        <section class="card form-section" id="related" aria-labelledby="related-h">
            <div class="card-head"><h2 id="related-h">Frequently bought</h2></div>
            <div class="card-body">
                <x-admin.field name="fq_bought_product_category_id" label="Suggest products from" hint="Shown under the product as “frequently bought together”.">
                    <select id="f-fq_bought_product_category_id" name="fq_bought_product_category_id" class="select">
                        <option value="">No suggestions</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected((int) $v('fq_bought_product_category_id', $product->fq_bought_product_category_id) === $c->id)>{{ str_repeat('— ', $c->level) }}{{ $c->name }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>
        </section>
    </div>

    <aside class="form-aside">
        <div class="card">
            <nav class="section-nav" aria-label="Form sections">
                @foreach ($sections as $id => [$label, $fields])
                    <a href="#{{ $id }}">
                        {{ $label }}
                        @if ($errors->hasAny($fields))<span class="dot-bad"><span class="sr-only">has errors</span></span>@endif
                    </a>
                @endforeach
            </nav>
            <div class="card-body aside-actions">
                <button type="submit" class="btn btn-primary btn-block">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-block">Cancel</a>
            </div>
            @if ($product->exists)
                <dl class="facts">
                    <div><dt>Owner</dt><dd>{{ $product->added_by === 'admin' ? 'In-house' : ($product->owner?->shop?->name ?? $product->owner?->name) }}</dd></div>
                    <div><dt>Shop status</dt><dd>
                        @if (! $product->approved)<span class="pill pill-warn">Awaiting approval</span>
                        @elseif ($product->published)<span class="pill pill-ok">Published</span>
                        @else<span class="pill pill-mute">Unpublished</span>@endif
                    </dd></div>
                    <div><dt>Sold</dt><dd class="num">{{ number_format($product->num_of_sale) }}</dd></div>
                    <div><dt>Slug</dt><dd class="mono">{{ $product->slug }}</dd></div>
                </dl>
            @endif
        </div>
    </aside>
</form>
