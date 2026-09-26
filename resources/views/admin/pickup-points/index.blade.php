@extends('admin.layouts.app')
@section('title', 'Pickup Points')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Pickup Points
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($points->isEmpty())
                <div class="empty">
                    <strong>No pickup points yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pickup point</th>
                                <th class="wrap">Address</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($points as $p)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.pickup-points.edit', $p) }}" class="product-name">{{ $p->name }}</a>
                                        <span class="faint mono">{{ $p->phone }}</span>
                                        @if (! $p->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="wrap">
                                    {{ $p->address['address'] ?? '' }}, {{ $p->address['city'] ?? '' }}
                                    <div class="product-meta">{{ $p->address['country'] ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.pickup-points.edit', $p) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.pickup-points.destroy', $p) }}"
                                              onsubmit="return confirm(@js('Delete the pickup point "'.$p->name.'"? This cannot be undone.'))">
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

        @if ($points->hasPages())
            <div style="margin-top:16px">{{ $points->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.pickup-points.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a pickup point</h2></div>
        <div class="card-body">
            @include('admin.pickup-points.fields', ['point' => $newPoint])
            <button type="submit" class="btn btn-primary btn-block">Add pickup point</button>
        </div>
    </form>
</div>

@endsection
