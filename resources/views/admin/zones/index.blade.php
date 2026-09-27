@extends('admin.layouts.app')
@section('title', 'Shipping Zones')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Shipping Zones
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($zones->isEmpty())
                <div class="empty">
                    <strong>No shipping zones yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Zone</th>
                                <th class="right">Countries</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($zones as $z)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.zones.edit', $z) }}" class="product-name">{{ $z->name }}</a>
                                        @if (! $z->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="right num">{{ $z->countries_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.zones.edit', $z) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.zones.destroy', $z) }}"
                                              onsubmit="return confirm(@js('Delete the zone "'.$z->name.'"? This cannot be undone.'))">
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

        @if ($zones->hasPages())
            <div style="margin-top:16px">{{ $zones->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.zones.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a zone</h2></div>
        <div class="card-body">
            @include('admin.zones.fields', ['zone' => $newZone])
            <button type="submit" class="btn btn-primary btn-block">Add zone</button>
        </div>
    </form>
</div>

@endsection
