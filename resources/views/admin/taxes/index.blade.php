@extends('admin.layouts.app')
@section('title', 'Vat & Tax')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Vat & Tax
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head"><h2>Taxes</h2></div>
            @if ($taxes->isEmpty())
                <div class="empty">
                    <strong>No taxes yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tax</th>
                                <th>Products</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($taxes as $t)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.taxes.edit', $t) }}" class="product-name">{{ $t->name }}</a>
                                        @if (! $t->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $t->product_taxes_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.taxes.edit', $t) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.taxes.destroy', $t) }}"
                                              onsubmit="return confirm(@js('Delete the tax "'.$t->name.'"? This cannot be undone.'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link btn-sm link-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($taxes->hasPages())
            <div style="margin-top:16px">{{ $taxes->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.taxes.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a tax</h2></div>
        <div class="card-body">
            @include('admin.taxes.fields', ['tax' => $newTax])
            <button type="submit" class="btn btn-primary btn-block">Add tax</button>
        </div>
    </form>
</div>

<div class="card" style="margin-top:20px">
    <div class="card-head"><h2>Product taxes</h2></div>
    @if ($assignments->isEmpty())
        <div class="empty">
            <strong>No product charges yet</strong>
            Charge a product under a tax with the form on the right.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Tax</th>
                        <th>Charge</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($assignments as $a)
                    <tr>
                        <td class="product-name">{{ $a->product?->name ?? 'product #'.$a->product_id }}</td>
                        <td>{{ $a->tax?->name ?? 'tax #'.$a->tax_id }}</td>
                        <td class="mono">{{ $a->tax }} {{ $a->tax_type === 'percent' ? '%' : '' }}</td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('admin.taxes.product-taxes.edit', $a) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.taxes.product-taxes.destroy', $a) }}"
                                      onsubmit="return confirm('Remove this charge from the product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm link-danger">Remove</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($assignments->hasPages())
    <div style="margin-top:16px">{{ $assignments->links() }}</div>
@endif

<form method="POST" action="{{ route('admin.taxes.product-taxes.store') }}" class="card" style="margin-top:20px; max-width:640px">
    @csrf
    <div class="card-head"><h2>Charge a product under a tax</h2></div>
    <div class="card-body">
        @include('admin.taxes.assign-fields', ['assignment' => $newAssignment, 'products' => $products, 'taxes' => $allTaxes])
        <button type="submit" class="btn btn-primary btn-block">Assign tax</button>
    </div>
</form>

@endsection
