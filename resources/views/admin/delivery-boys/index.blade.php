@extends('admin.layouts.app')
@section('title', 'Delivery Boys')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Delivery Boys
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head">
                <h2>Delivery boys</h2>
                <a href="{{ route('admin.delivery-boys.payments') }}" class="btn btn-link btn-sm">Payments</a>
                <a href="{{ route('admin.delivery-boys.collections') }}" class="btn btn-link btn-sm">Collections</a>
            </div>
            @if ($boys->isEmpty())
                <div class="empty">
                    <strong>No delivery boys yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Delivery boy</th>
                                <th>Contact</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($boys as $b)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.delivery-boys.edit', $b) }}" class="product-name">{{ $b->name }}</a>
                                        @if ($b->banned)
                                            <span class="pill pill-mute">banned</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ $b->email }}
                                    <div class="product-meta mono">{{ $b->phone }}</div>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.delivery-boys.edit', $b) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.delivery-boys.destroy', $b) }}"
                                              onsubmit="return confirm(@js('Delete the delivery boy "'.$b->name.'"? This cannot be undone.'))">
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

        @if ($boys->hasPages())
            <div style="margin-top:16px">{{ $boys->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.delivery-boys.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a delivery boy</h2></div>
        <div class="card-body">
            @include('admin.delivery-boys.fields', ['boy' => $newBoy])
            <button type="submit" class="btn btn-primary btn-block">Add delivery boy</button>
        </div>
    </form>
</div>

@endsection
