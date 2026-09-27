@extends('admin.layouts.app')
@section('title', 'Shipping States')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Shipping States
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head">
                <h2>States</h2>
                <form method="GET" action="{{ route('admin.states.index') }}" style="display:flex;gap:8px">
                    <select name="country" class="select-sm" aria-label="Filter by country">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->id }}" @selected($countryId === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
                </form>
            </div>
            @if ($states->isEmpty())
                <div class="empty">
                    <strong>No shipping states yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>State</th>
                                <th>Country</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($states as $s)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.states.edit', $s) }}" class="product-name">{{ $s->name }}</a>
                                        @if (! $s->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ $s->country?->name ?? '—' }}
                                    <div class="product-meta mono">{{ $s->country?->code }}</div>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.states.edit', $s) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.states.destroy', $s) }}"
                                              onsubmit="return confirm(@js('Delete the state "'.$s->name.'"? This cannot be undone.'))">
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

        @if ($states->hasPages())
            <div style="margin-top:16px">{{ $states->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.states.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a state</h2></div>
        <div class="card-body">
            @include('admin.states.fields', ['state' => $newState])
            <button type="submit" class="btn btn-primary btn-block">Add state</button>
        </div>
    </form>
</div>

@endsection
