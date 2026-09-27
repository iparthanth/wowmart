@extends('admin.layouts.app')
@section('title', $carrier->name)
@section('crumbs')
    <a href="{{ route('admin.carriers.index') }}">Shipping Carriers</a> / Edit
@endsection

@section('content')

<div class="split">
    <div style="display:grid;gap:18px;min-width:0">
        <section class="card">
            <div class="card-head"><h2>Billing ranges</h2></div>
            @if ($carrier->ranges->isEmpty())
                <div class="empty">
                    <strong>No ranges yet</strong>
                    A range is a billing window - by shipment weight or price - priced per zone.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Billed by</th>
                                <th class="right">From</th>
                                <th class="right">To</th>
                                <th class="wrap">Zone prices</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($carrier->ranges as $range)
                            <tr>
                                <td>{{ $range->billing_type === 'weight' ? 'Weight' : 'Order price' }}</td>
                                <td class="right num">{{ $range->lower }}</td>
                                <td class="right num">{{ $range->upper ?? 'and up' }}</td>
                                <td class="wrap">
                                    @forelse ($range->prices as $price)
                                        <span class="pill pill-info">{{ $price->zone?->name ?? '?' }}: ৳{{ number_format($price->price, 2) }}</span>
                                        <form method="POST" action="{{ route('admin.carriers.ranges.prices.destroy', $price) }}" class="inline-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link btn-sm link-danger" title="Remove this zone price">×</button>
                                        </form>
                                    @empty
                                        <span class="muted">No zone prices yet</span>
                                    @endforelse
                                    <form method="POST" action="{{ route('admin.carriers.ranges.prices.store', $range) }}"
                                          style="display:flex;gap:8px;margin-top:8px;align-items:center">
                                        @csrf
                                        <select name="zone_id" class="select-sm" required aria-label="Zone">
                                            @foreach ($zones as $z)
                                                @continue($range->prices->contains('zone_id', $z->id))
                                                <option value="{{ $z->id }}">{{ $z->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="price" class="input-sm" step="0.01" min="0" required placeholder="Price" aria-label="Price">
                                        <button type="submit" class="btn btn-ghost btn-sm">Add price</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.carriers.ranges.destroy', $range) }}"
                                          onsubmit="return confirm(@js('Delete this range? Its zone prices go with it.'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-sm link-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card">
            <div class="card-head"><h2>Add a range</h2></div>
            <form method="POST" action="{{ route('admin.carriers.ranges.store', $carrier) }}" class="card-body">
                @csrf
                <div class="field">
                    <label for="f-billing_type">Billed by</label>
                    <select id="f-billing_type" name="billing_type" class="input" required>
                        <option value="weight">Shipment weight</option>
                        <option value="price">Order price</option>
                    </select>
                </div>
                <div class="split" style="gap:12px">
                    <x-admin.field name="lower" label="From" type="number" step="0.01" min="0" required />
                    <x-admin.field name="upper" label="To" type="number" step="0.01" min="0"
                                   hint="Leave empty for an open range - and up." />
                </div>
                <button type="submit" class="btn btn-primary">Add range</button>
            </form>
        </section>
    </div>

    <form method="POST" action="{{ route('admin.carriers.update', $carrier) }}" class="card">
        @csrf
        @method('PUT')
        <div class="card-head"><h2>Edit carrier</h2></div>
        <div class="card-body">
            @include('admin.carriers.fields', ['carrier' => $carrier])
            <div class="row-actions" style="justify-content:flex-start">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="{{ route('admin.carriers.index') }}" class="btn btn-link">Back to carriers</a>
            </div>
        </div>
    </form>
</div>

@endsection
