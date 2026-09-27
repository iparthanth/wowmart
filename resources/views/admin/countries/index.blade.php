@extends('admin.layouts.app')
@section('title', 'Shipping Countries')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Shipping Countries
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($countries->isEmpty())
                <div class="empty">
                    <strong>No shipping countries yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th class="right">States</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($countries as $c)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.countries.edit', $c) }}" class="product-name">{{ $c->name }}</a>
                                        <span class="faint mono">{{ $c->code }}</span>
                                        @if (! $c->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="right num">{{ $c->states_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.countries.edit', $c) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.countries.destroy', $c) }}"
                                              onsubmit="return confirm(@js('Delete the country "'.$c->name.'"? This cannot be undone.'))">
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

        @if ($countries->hasPages())
            <div style="margin-top:16px">{{ $countries->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.countries.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a country</h2></div>
        <div class="card-body">
            @include('admin.countries.fields', ['country' => $newCountry])
            <button type="submit" class="btn btn-primary btn-block">Add country</button>
        </div>
    </form>
</div>

@endsection
