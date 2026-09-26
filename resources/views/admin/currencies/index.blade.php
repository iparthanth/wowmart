@extends('admin.layouts.app')
@section('title', 'Currency')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Currency
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($currencies->isEmpty())
                <div class="empty">
                    <strong>No currencies yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Currency</th>
                                <th>Rate</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($currencies as $c)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.currencies.edit', $c) }}" class="product-name">{{ $c->name }}</a>
                                        <span class="faint">{{ $c->code }} {{ $c->symbol }}</span>
                                        @if ($c->id === $systemId)
                                            <span class="pill pill-info">system</span>
                                        @elseif (! $c->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="mono">{{ $c->exchange_rate }}</td>
                                <td>
                                    <div class="row-actions">
                                        @if ($c->id !== $systemId)
                                            <form method="POST" action="{{ route('admin.currencies.system', $c) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-sm"
                                                        title="Make this the system currency">Set as system</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.currencies.destroy', $c) }}"
                                                  onsubmit="return confirm(@js('Delete the currency "'.$c->name.'"? This cannot be undone.'))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link btn-sm link-danger">Delete</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.currencies.edit', $c) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($currencies->hasPages())
            <div style="margin-top:16px">{{ $currencies->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.currencies.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a currency</h2></div>
        <div class="card-body">
            @include('admin.currencies.fields', ['currency' => $newCurrency])
            <button type="submit" class="btn btn-primary btn-block">Add currency</button>
        </div>
    </form>
</div>

@endsection
