{{--
    The product-tax assignment fields. Expects $assignment (new or stored) and $taxes (all, id+name).
    $products (id+name) is passed only for the "add" form; editing keeps its product.
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

@if (isset($products))
    <x-admin.field name="product_id" label="Product" :hint="$assignment->product_id ? '' : 'Pick the product to charge.'">
        <select id="f-product_id" name="product_id" class="input" required>
            <option value="">- choose a product -</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}" @selected((string) $v('product_id', $assignment->product_id) === (string) $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </x-admin.field>
@else
    <input type="hidden" name="product_id" value="{{ $assignment->product_id }}">
    <div class="field">
        <label>Product</label>
        <p class="mono">{{ $assignment->product?->name ?? 'product #'.$assignment->product_id }}</p>
    </div>
@endif

<x-admin.field name="tax_id" label="Tax">
    <select id="f-tax_id" name="tax_id" class="input" required>
        <option value="">- choose a tax -</option>
        @foreach ($taxes as $t)
            <option value="{{ $t->id }}" @selected((string) $v('tax_id', $assignment->tax_id) === (string) $t->id)>{{ $t->name }}</option>
        @endforeach
    </select>
</x-admin.field>

<x-admin.field name="tax" label="Amount" type="number" step="0.01" min="0" :value="$v('tax', $assignment->tax ?? '')" required
               hint="Read according to the type below." />

<div class="field">
    <label for="f-tax_type">Type</label>
    <select id="f-tax_type" name="tax_type" class="input" required>
        @foreach (\App\Models\ProductTax::TYPES as $type)
            <option value="{{ $type }}" @selected((string) $v('tax_type', $assignment->tax_type ?? 'percent') === $type)>{{ ucfirst($type) }}</option>
        @endforeach
    </select>
</div>
