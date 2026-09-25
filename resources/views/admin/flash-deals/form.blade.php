{{--
    The flash deal form, for create and edit. Products are a plain table
    (no JavaScript): tick "Include" and set that product's discount.
--}}
@use('App\Enums\DiscountType')
@use('App\Models\FlashDeal')
@extends('admin.layouts.app')
@section('title', $deal->exists ? $deal->title : 'Add new flash deal')
@section('crumbs')
    <a href="{{ route('admin.flash-deals.index') }}">Flash deals</a> / {{ $deal->exists ? 'Edit' : 'New' }}
@endsection

@section('content')
@php
    // After a failed save show what was typed; otherwise show what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $date = fn ($x) => $x?->format('Y-m-d\TH:i');
    // product id => [discount, discount_type], included rows only.
    $rows = $fresh
        ? $deal->products->mapWithKeys(fn ($p) => [$p->id => ['discount' => $p->pivot->discount, 'discount_type' => $p->pivot->discount_type]])->all()
        : array_filter((array) old('products', []), fn ($r) => is_array($r) && ! empty($r['include']));
@endphp

<form method="POST" action="{{ $deal->exists ? route('admin.flash-deals.update', $deal) : route('admin.flash-deals.store') }}" class="form-layout">
    @csrf
    @if ($deal->exists)
        @method('PUT')
    @endif

    <div class="form-main">
        @if ($errors->any())
            <div class="flash flash-bad" role="alert">
                {{ $errors->count() === 1 ? 'One field needs' : $errors->count().' fields need' }} fixing before this can be saved.
            </div>
        @endif

        <section class="card form-section" aria-labelledby="deal-h">
            <div class="card-head"><h2 id="deal-h">Deal</h2></div>
            <div class="card-body">
                <x-admin.field name="title" label="Title" :value="$v('title', $deal->title)" required maxlength="150"
                               hint="Shown as the heading of the deal's page in the shop." />

                <div class="grid-2">
                    <x-admin.field name="start_date" label="Starts" type="datetime-local" :value="$v('start_date', $date($deal->start_date))" required />
                    <x-admin.field name="end_date" label="Ends" type="datetime-local" :value="$v('end_date', $date($deal->end_date))" required />
                </div>

                <x-admin.field name="banner" label="Banner" hint="A wide image for the top of the deal's page.">
                    <x-admin.media-picker name="banner" :images="$images" :selected="$v('banner', $deal->banner)" />
                </x-admin.field>

                <div class="grid-2">
                    <x-admin.field name="background_color" label="Background colour" type="color" :value="$v('background_color', $deal->background_color ?? '#14213D')" />
                    <x-admin.field name="text_color" label="Text colour" type="color" :value="$v('text_color', $deal->text_color ?? '#FFFFFF')" />
                </div>

                <div class="checks">
                    <label class="check"><input type="checkbox" name="status" value="1" @checked($v('status', $deal->status))> Active</label>
                    <label class="check"><input type="checkbox" name="featured" value="1" @checked($v('featured', $deal->featured))> Featured (replaces the current featured deal)</label>
                </div>
            </div>
        </section>

        <section class="card form-section" aria-labelledby="products-h">
            <div class="card-head"><h2 id="products-h">Products</h2></div>
            <div class="card-body">
                <p class="hint" style="margin-top:0">Tick the products in this deal and set each one's discount. A fixed amount cannot be more than the product's price. A product can be in one flash deal at a time.</p>
                @error('products')
                    <p class="error" role="alert">{{ $message }}</p>
                @enderror
                <div class="table-wrap deal-products">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="tight">Include</th>
                                <th>Product</th>
                                <th class="right">Price</th>
                                <th>Discount</th>
                                <th>Discount is</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($products as $p)
                            @php
                                $row = $rows[$p->id] ?? null;
                                $rowError = $errors->first("products.{$p->id}.discount") ?: $errors->first("products.{$p->id}.discount_type");
                                // A product can be in one deal at a time; say where it is so the refusal is no surprise.
                                $elsewhere = $p->flashDeals->firstWhere('id', '!=', $deal->id);
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" id="inc-{{ $p->id }}" name="products[{{ $p->id }}][include]" value="1" @checked($row !== null)>
                                </td>
                                <td>
                                    <label for="inc-{{ $p->id }}" class="product-cell">
                                        @if ($p->thumbnail)
                                            <img src="{{ $p->thumbnail->url() }}" alt="" class="thumb" loading="lazy">
                                        @else
                                            <span class="thumb thumb-missing" title="No image"></span>
                                        @endif
                                        <span>
                                            <span class="product-name">{{ $p->name }}</span>
                                            @if ($elsewhere)
                                                <span class="product-meta">In "{{ $elsewhere->title }}"</span>
                                            @endif
                                        </span>
                                    </label>
                                </td>
                                <td class="right num">৳{{ number_format((float) $p->unit_price, 2) }}</td>
                                <td>
                                    <input type="number" name="products[{{ $p->id }}][discount]" value="{{ $row['discount'] ?? '' }}"
                                           class="input" step="0.01" min="0" aria-label="Discount for {{ $p->name }}"
                                           @if ($rowError) aria-invalid="true" @endif>
                                    @if ($rowError)<span class="error">{{ $rowError }}</span>@endif
                                </td>
                                <td>
                                    <select name="products[{{ $p->id }}][discount_type]" class="select" aria-label="Discount type for {{ $p->name }}">
                                        @foreach (DiscountType::cases() as $t)
                                            <option value="{{ $t->value }}" @selected(($row['discount_type'] ?? 'percent') === $t->value)>{{ $t === DiscountType::Percent ? 'Percent' : 'Amount (৳)' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <aside class="form-aside">
        <div class="card">
            <div class="card-body aside-actions">
                <button type="submit" class="btn btn-primary btn-block">{{ $deal->exists ? 'Save changes' : 'Create flash deal' }}</button>
                <a href="{{ route('admin.flash-deals.index') }}" class="btn btn-ghost btn-block">Cancel</a>
            </div>
            @if ($deal->exists)
                @php [$stateLabel, $stateClass] = FlashDeal::STATES[$deal->state()]; @endphp
                <dl class="facts">
                    <div><dt>State</dt><dd><span class="pill {{ $stateClass }}">{{ $stateLabel }}</span></dd></div>
                    <div><dt>Products</dt><dd class="num">{{ $deal->products->count() }}</dd></div>
                    <div><dt>Page</dt><dd><a href="{{ $deal->pageUrl() }}" target="_blank" rel="noopener">Open in shop<span class="sr-only"> (new tab)</span></a></dd></div>
                </dl>
                <p class="hint" style="margin:0;padding:0 18px 14px">The page link stays the same if you rename the deal.</p>
            @endif
        </div>
    </aside>
</form>

@endsection
