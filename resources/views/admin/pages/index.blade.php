@extends('admin.layouts.app')
@section('title', 'Pages')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Website Setup / Pages
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($pages->isEmpty())
                <div class="empty">
                    <strong>No pages yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Page</th>
                                <th>Type</th>
                                <th>Address</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($pages as $p)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.pages.edit', $p) }}" class="product-name">{{ $p->title }}</a>
                                        @if (! $p->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="pill pill-info">{{ ucfirst($p->type) }}</span></td>
                                <td class="muted mono">/page/{{ $p->slug }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.pages.destroy', $p) }}"
                                              onsubmit="return confirm(@js('Delete the page "'.$p->title.'"? This cannot be undone.'))">
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

        @if ($pages->hasPages())
            <div style="margin-top:16px">{{ $pages->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.pages.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a page</h2></div>
        <div class="card-body">
            @include('admin.pages.fields', ['page' => $newPage])
            <button type="submit" class="btn btn-primary btn-block">Add page</button>
        </div>
    </form>
</div>

@endsection
