@extends('admin.layouts.app')
@section('title', 'Warranties')
@section('crumbs')
    Warranties
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($warranties->isEmpty())
                <div class="empty">
                    <strong>No warranties yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Warranty</th>
                                <th class="right">Products</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($warranties as $w)
                            <tr>
                                <td><a href="{{ route('admin.warranties.edit', $w) }}" class="product-name">{{ $w->text }}</a></td>
                                <td class="right num">{{ number_format($w->products_count) }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.warranties.edit', $w) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.warranties.destroy', $w) }}"
                                              onsubmit="return confirm(@js('Delete the warranty "'.$w->text.'"? This cannot be undone.'))">
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

        @if ($warranties->hasPages())
            <div style="margin-top:16px">{{ $warranties->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.warranties.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a warranty</h2></div>
        <div class="card-body">
            <x-admin.field name="text" label="Warranty" :value="old('text')" required maxlength="100" hint="Like 1 year or 6 months replacement." />
            <button type="submit" class="btn btn-primary btn-block">Add warranty</button>
        </div>
    </form>
</div>

@endsection
