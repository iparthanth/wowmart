@extends('admin.layouts.app')
@section('title', 'Shipping Carriers')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Shipping Carriers
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($carriers->isEmpty())
                <div class="empty">
                    <strong>No shipping carriers yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Carrier</th>
                                <th>Transit time</th>
                                <th class="right">Ranges</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($carriers as $c)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.carriers.edit', $c) }}" class="product-name">{{ $c->name }}</a>
                                        @if ($c->free_shipping)
                                            <span class="pill pill-info">free shipping</span>
                                        @elseif (! $c->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="muted">{{ $c->transit_time ?? '—' }}</td>
                                <td class="right num">{{ $c->ranges_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.carriers.edit', $c) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.carriers.destroy', $c) }}"
                                              onsubmit="return confirm(@js('Delete the carrier "'.$c->name.'"? Its billing ranges and zone prices go with it. This cannot be undone.'))">
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

        @if ($carriers->hasPages())
            <div style="margin-top:16px">{{ $carriers->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.carriers.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a carrier</h2></div>
        <div class="card-body">
            @include('admin.carriers.fields', ['carrier' => $newCarrier])
            <button type="submit" class="btn btn-primary btn-block">Add carrier</button>
        </div>
    </form>
</div>

@endsection
