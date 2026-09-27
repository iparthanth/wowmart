@extends('admin.layouts.app')
@section('title', 'Custom Alert')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Marketing / Custom Alert
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($alerts->isEmpty())
                <div class="empty">
                    <strong>No alerts yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Alert</th>
                                <th>Style</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($alerts as $a)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.marketing.alerts.edit', $a) }}" class="product-name">{{ $a->title }}</a>
                                        @if (! $a->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="pill pill-info">{{ ucfirst($a->style) }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.marketing.alerts.edit', $a) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.marketing.alerts.destroy', $a) }}"
                                              onsubmit="return confirm(@js('Delete the alert "'.$a->title.'"? This cannot be undone.'))">
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

        @if ($alerts->hasPages())
            <div style="margin-top:16px">{{ $alerts->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.marketing.alerts.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add an alert</h2></div>
        <div class="card-body">
            @include('admin.marketing.alert-fields', ['alert' => $newAlert])
            <button type="submit" class="btn btn-primary btn-block">Add alert</button>
        </div>
    </form>
</div>

@endsection
