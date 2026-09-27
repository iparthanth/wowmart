@extends('admin.layouts.app')
@section('title', 'Blog Categories')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Blog System / Categories
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($categories->isEmpty())
                <div class="empty">
                    <strong>No blog categories yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="right">Posts</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($categories as $c)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.blog-categories.edit', $c) }}" class="product-name">{{ $c->name }}</a>
                                        @if (! $c->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="right num">{{ $c->posts_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.blog-categories.edit', $c) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.blog-categories.destroy', $c) }}"
                                              onsubmit="return confirm(@js('Delete the category "'.$c->name.'"? This cannot be undone.'))">
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

        @if ($categories->hasPages())
            <div style="margin-top:16px">{{ $categories->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.blog-categories.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a category</h2></div>
        <div class="card-body">
            @include('admin.blog.category-fields', ['category' => $newCategory])
            <button type="submit" class="btn btn-primary btn-block">Add category</button>
        </div>
    </form>
</div>

@endsection
