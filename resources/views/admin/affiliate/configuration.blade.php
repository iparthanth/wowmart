@extends('admin.layouts.app')
@section('title', 'Affiliate Configurations')
@section('crumbs')
    Affiliate System / Configurations
@endsection

@section('content')

@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
    $commissions = json_decode($v('affiliate_category_commissions', $settings['affiliate_category_commissions']) ?: '[]', true) ?? [];
    $sharingOn = $v('affiliate_sharing_status', $settings['affiliate_sharing_status']) === '1';
    $categoryOn = $v('affiliate_category_status', $settings['affiliate_category_status']) === '1';
@endphp

<form method="POST" action="{{ route('admin.affiliate.configurations') }}">
    @csrf
    <div class="split">
        <div class="card">
            <div class="card-head"><h2>User registration &amp; first purchase</h2></div>
            <div class="card-body">
                <p class="muted">Earn when a referred user signs up and again on their first completed order.</p>
                <div class="field-row">
                    <x-admin.field name="affiliate_first_purchase_value" label="Commission value" type="number" step="0.01" min="0" :value="$v('affiliate_first_purchase_value', $settings['affiliate_first_purchase_value'])" required />
                    <div class="field">
                        <label for="fp-type">Commission type</label>
                        <select id="fp-type" name="affiliate_first_purchase_type" class="input">
                            <option value="percent" @selected($v('affiliate_first_purchase_type', $settings['affiliate_first_purchase_type']) === 'percent')>%</option>
                            <option value="amount" @selected($v('affiliate_first_purchase_type', $settings['affiliate_first_purchase_type']) === 'amount')>$ fixed</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fp-status">Status</label>
                        <select id="fp-status" name="affiliate_first_purchase_status" class="input">
                            <option value="1" @selected($v('affiliate_first_purchase_status', $settings['affiliate_first_purchase_status']) === '1')>Enabled</option>
                            <option value="0" @selected($v('affiliate_first_purchase_status', $settings['affiliate_first_purchase_status']) === '0')>Disabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Product sharing &amp; purchasing</h2></div>
            <div class="card-body">
                <p class="muted">Earn every time a buyer completes an order that came through the affiliate's shared product link.</p>
                <div class="field-row">
                    <x-admin.field name="affiliate_sharing_value" label="Commission value" type="number" step="0.01" min="0" :value="$v('affiliate_sharing_value', $settings['affiliate_sharing_value'])" required />
                    <div class="field">
                        <label for="sh-type">Commission type</label>
                        <select id="sh-type" name="affiliate_sharing_type" class="input">
                            <option value="percent" @selected($v('affiliate_sharing_type', $settings['affiliate_sharing_type']) === 'percent')>%</option>
                            <option value="amount" @selected($v('affiliate_sharing_type', $settings['affiliate_sharing_type']) === 'amount')>$ fixed</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="sh-status">Status</label>
                        <select id="sh-status" name="affiliate_sharing_status" class="input">
                            <option value="1" @selected($sharingOn)>Enabled</option>
                            <option value="0" @selected(! $sharingOn)>Disabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:16px">
        <div class="card-head"><h2>Category wise affiliate</h2></div>
        <div class="card-body">
            <div class="field" style="max-width:280px">
                <label for="cat-status">Status</label>
                <select id="cat-status" name="affiliate_category_status" class="input">
                    <option value="1" @selected($categoryOn)>Enabled</option>
                    <option value="0" @selected(! $categoryOn)>Disabled</option>
                </select>
            </div>
            <p class="muted">A flat commission per category. Product sharing and category wise cannot be enabled together.</p>
            <div id="category-rows">
                @foreach ($commissions as $i => $commission)
                    <div class="field-row" style="margin-bottom:8px">
                        <div class="field" style="flex:2">
                            <label>Category</label>
                            <select name="affiliate_category_commissions[{{ $i }}][category_id]" class="input">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) ($commission['category_id'] ?? 0) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Type</label>
                            <select name="affiliate_category_commissions[{{ $i }}][type]" class="input">
                                <option value="percent" @selected(($commission['type'] ?? '') === 'percent')>%</option>
                                <option value="amount" @selected(($commission['type'] ?? '') === 'amount')>$ fixed</option>
                            </select>
                        </div>
                        <x-admin.field name="affiliate_category_commissions[{{ $i }}][value]" label="Value" type="number" step="0.01" min="0" :value="(string) ($commission['value'] ?? '')" />
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-ghost btn-sm" id="add-category"
                    data-template="{{ trim(view('admin.affiliate.category-row', ['i' => 'ROW', 'categories' => $categories])->render()) }}">
                Add category commission
            </button>
        </div>
    </div>

    <div class="card" style="margin-top:16px">
        <div class="card-head"><h2>Validation time</h2></div>
        <div class="card-body">
            <div class="field-row">
                <x-admin.field name="affiliate_validation_days" label="Days before a commission becomes withdrawable" type="number" min="1" max="3650" :value="$v('affiliate_validation_days', $settings['affiliate_validation_days'])" required />
            </div>
            <button type="submit" class="btn btn-primary">Save configurations</button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('add-category').addEventListener('click', function () {
        var holder = document.getElementById('category-rows');
        var wrap = document.createElement('div');
        wrap.innerHTML = this.dataset.template.replace(/ROW/g, String(holder.children.length));
        holder.appendChild(wrap.firstElementChild);
    });
</script>
@endpush
