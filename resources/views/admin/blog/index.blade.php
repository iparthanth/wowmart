@extends('admin.layouts.app')
@section('title', 'Blog Posts')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Blog System / All Posts
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($posts->isEmpty())
                <div class="empty">
                    <strong>No blog posts yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Post</th>
                                <th>Category</th>
                                <th>Address</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($posts as $p)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.blog.edit', $p) }}" class="product-name">{{ $p->title }}</a>
                                        @if (! $p->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $p->category?->name ?? '—' }}</td>
                                <td class="muted mono">/blog/{{ $p->slug }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.blog.edit', $p) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.blog.destroy', $p) }}"
                                              onsubmit="return confirm(@js('Delete the post "'.$p->title.'"? This cannot be undone.'))">
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

        @if ($posts->hasPages())
            <div style="margin-top:16px">{{ $posts->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.blog.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a post</h2></div>
        <div class="card-body">
            @include('admin.blog.fields', ['post' => $newPost])
            <button type="submit" class="btn btn-primary btn-block">Add post</button>
        </div>
    </form>
</div>

@endsection
