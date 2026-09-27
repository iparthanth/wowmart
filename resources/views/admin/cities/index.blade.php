@extends('admin.layouts.app')
@section('title', 'Shipping Cities')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Shipping Cities
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head">
                <h2>Cities</h2>
                <form method="GET" action="{{ route('admin.cities.index') }}" style="display:flex;gap:8px">
                    <select name="state" class="select-sm" aria-label="Filter by state">
                        <option value="">All states</option>
                        @foreach ($states as $s)
                            <option value="{{ $s->id }}" @selected($stateId === $s->id)>{{ $s->name }} - {{ $s->country?->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
                </form>
            </div>
            @if ($cities->isEmpty())
                <div class="empty">
                    <strong>No shipping cities yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>City</th>
                                <th>State</th>
                                <th class="right">Cost</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($cities as $c)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.cities.edit', $c) }}" class="product-name">{{ $c->name }}</a>
                                        @if (! $c->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ $c->state?->name ?? '—' }}
                                    <div class="product-meta">{{ $c->state?->country?->name }}</div>
                                </td>
                                <td class="right num" style="white-space:nowrap">৳{{ number_format($c->cost, 2) }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.cities.edit', $c) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.cities.destroy', $c) }}"
                                              onsubmit="return confirm(@js('Delete the city "'.$c->name.'"? This cannot be undone.'))">
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

        @if ($cities->hasPages())
            <div style="margin-top:16px">{{ $cities->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.cities.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a city</h2></div>
        <div class="card-body">
            @include('admin.cities.fields', ['city' => $newCity])
            <button type="submit" class="btn btn-primary btn-block">Add city</button>
        </div>
    </form>
</div>

@endsection
