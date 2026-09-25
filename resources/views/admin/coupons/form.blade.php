{{--
    The coupon form for one type, used by create (?type=...) and edit.
    Shared fields first, then the fields that go into this type's details.
--}}
@use('App\Enums\CouponType')
@use('App\Enums\DiscountType')
@extends('admin.layouts.app')
@section('title', $coupon->exists ? $coupon->code : 'Add new coupon')
@section('crumbs')
    <a href="{{ route('admin.coupons.index') }}">Coupons</a> / {{ $coupon->exists ? 'Edit' : 'New' }}
@endsection

@section('content')
@php
    // After a failed save show what was typed; otherwise show what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $d = $coupon->details ?? [];
    $date = fn ($x) => $x?->format('Y-m-d\TH:i');
    $chosen = array_map('intval', (array) $v('product_ids', $d['product_ids'] ?? []));
@endphp

<form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="form-layout">
    @csrf
    @if ($coupon->exists)
        @method('PUT')
    @else
        <input type="hidden" name="type" value="{{ $coupon->type->value }}">
    @endif

    <div class="form-main">
        @if ($errors->any())
            <div class="flash flash-bad" role="alert">
                {{ $errors->count() === 1 ? 'One field needs' : $errors->count().' fields need' }} fixing before this can be saved.
            </div>
        @endif

        <section class="card form-section" aria-labelledby="coupon-h">
            <div class="card-head"><h2 id="coupon-h">{{ $coupon->type->label() }} coupon</h2></div>
            <div class="card-body">
                <x-admin.field name="code" label="Code" :value="$v('code', $coupon->code)" required maxlength="32"
                               class="mono" autocomplete="off" hint="Letters, digits and dashes. Saved in capitals; shoppers can type it in any case." />

                <div class="grid-2">
                    <x-admin.field name="discount" label="Discount" type="number" :value="$v('discount', $coupon->discount)" required step="0.01" min="0.01" />
                    <x-admin.field name="discount_type" label="Discount is">
                        <select id="f-discount_type" name="discount_type" class="select" required>
                            @foreach (DiscountType::cases() as $t)
                                <option value="{{ $t->value }}" @selected($v('discount_type', $coupon->discount_type?->value) === $t->value)>{{ $t === DiscountType::Percent ? 'A percentage (up to 100)' : 'An amount in ৳' }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                </div>

                <div class="grid-2">
                    <x-admin.field name="start_date" label="Starts" type="datetime-local" :value="$v('start_date', $date($coupon->start_date))" required max="2038-01-18T23:59" />
                    <x-admin.field name="end_date" label="Ends" type="datetime-local" :value="$v('end_date', $date($coupon->end_date))" required max="2038-01-18T23:59" />
                </div>

                <div class="checks">
                    <label class="check"><input type="checkbox" name="status" value="1" @checked($v('status', $coupon->status))> Active</label>
                </div>
            </div>
        </section>

        <section class="card form-section" aria-labelledby="rules-h">
            <div class="card-head"><h2 id="rules-h">Where it applies</h2></div>
            <div class="card-body">
                @if ($coupon->type === CouponType::ProductBase)
                    <fieldset class="field">
                        <legend class="field-label">Products</legend>
                        <div class="check-list">
                            @foreach ($products as $p)
                                <label class="check">
                                    <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" @checked(in_array($p->id, $chosen, true))>
                                    <span>{{ $p->name }} @if ($p->sku)<span class="faint mono">{{ $p->sku }}</span>@endif</span>
                                </label>
                            @endforeach
                        </div>
                        <span class="hint">The discount comes off each chosen product in the cart.</span>
                        @if ($errors->has('product_ids') || $errors->has('product_ids.*'))
                            <span class="error">{{ $errors->first('product_ids') ?: $errors->first('product_ids.*') }}</span>
                        @endif
                    </fieldset>
                @elseif ($coupon->type === CouponType::CartBase)
                    <div class="grid-2">
                        <x-admin.field name="min_buy" label="Minimum spend (৳)" type="number" :value="$v('min_buy', $d['min_buy'] ?? 0)" required step="0.01" min="0"
                                       hint="The cart total needed before the code works. 0 for any cart." />
                        <x-admin.field name="max_discount" label="Maximum discount (৳)" type="number" :value="$v('max_discount', $d['max_discount'] ?? null)" step="0.01" min="0.01"
                                       hint="Only matters for a percentage discount: caps what it can take off. Empty for no cap." />
                    </div>
                @else
                    <div class="grid-2">
                        <x-admin.field name="validation_days" label="Valid for (days)" type="number" :value="$v('validation_days', $d['validation_days'] ?? 30)" required step="1" min="1" max="365"
                                       hint="Days after sign-up the coupon stays valid." />
                        <x-admin.field name="min_buy" label="Minimum spend (৳)" type="number" :value="$v('min_buy', $d['min_buy'] ?? 0)" step="0.01" min="0"
                                       hint="0 for any first order." />
                    </div>
                @endif
            </div>
        </section>
    </div>

    <aside class="form-aside">
        <div class="card">
            <div class="card-body aside-actions">
                <button type="submit" class="btn btn-primary btn-block">{{ $coupon->exists ? 'Save changes' : 'Create coupon' }}</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-ghost btn-block">Cancel</a>
            </div>
            <dl class="facts">
                <div><dt>Type</dt><dd>{{ $coupon->type->label() }}</dd></div>
                @if ($coupon->exists)
                    @php [$stateLabel, $stateClass] = \App\Models\Coupon::STATES[$coupon->state()]; @endphp
                    <div><dt>State</dt><dd><span class="pill {{ $stateClass }}">{{ $stateLabel }}</span></dd></div>
                    <div><dt>Times used</dt><dd class="num">{{ number_format($coupon->usages_count) }}</dd></div>
                @endif
            </dl>
            <p class="hint" style="margin:0;padding:0 18px 14px">
                @if ($coupon->exists)
                    The type is fixed once a coupon exists. For a different type, create a new coupon.
                @else
                    The type cannot change after saving. <a href="{{ route('admin.coupons.create') }}">Choose another type</a>
                @endif
            </p>
        </div>
    </aside>
</form>

@endsection
