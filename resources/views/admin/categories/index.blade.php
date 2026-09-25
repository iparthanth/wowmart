@extends('admin.layouts.app')
@section('title', 'Categories')
@section('crumbs')
    Products / Categories
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        {{ number_format($categories->count()) }} {{ Str::plural('category', $categories->count()) }}{{ $search !== '' ? ' matching "'.$search.'"' : '' }}.
    </p>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Add new category</a>
</div>

<form method="GET" action="{{ route('admin.categories.index') }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $search }}" class="input filter-search" placeholder="Category name">
    <button type="submit" class="btn btn-ghost">Search</button>
    @if ($search !== '')
        <a href="{{ route('admin.categories.index') }}" class="btn btn-link">Show the whole tree</a>
    @endif
</form>

<div class="card">
    @if ($categories->isEmpty())
        <div class="empty">
            @if ($search !== '')
                <strong>No category matches</strong>
                Try another name, or <a href="{{ route('admin.categories.index') }}" style="color:var(--tangerine)">see every category</a>.
            @else
                <strong>No categories yet</strong>
                <a href="{{ route('admin.categories.create') }}" style="color:var(--tangerine)">Add the first one</a>.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Parent</th>
                        <th class="right wrap">Order level</th>
                        <th class="right">Products</th>
                        <th class="wrap">Featured</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($categories as $c)
                    <tr>
                        <td>
                            {{-- Indented only in the tree; search results are a flat list. --}}
                            <div class="product-cell" @if ($search === '') style="padding-left: {{ $c->level * 24 }}px" @endif>
                                @if ($c->iconFile)
                                    <img src="{{ $c->iconFile->url() }}" alt="" class="thumb" loading="lazy">
                                @else
                                    <span class="thumb thumb-missing" title="No icon"></span>
                                @endif
                                <a href="{{ route('admin.categories.edit', $c) }}" class="product-name">{{ $c->name }}</a>
                            </div>
                        </td>
                        <td class="muted">{{ $c->parent?->name ?? '—' }}</td>
                        <td class="right num">{{ $c->order_level }}</td>
                        <td class="right num">{{ number_format($productCounts[$c->id] ?? 0) }}</td>
                        <td><x-admin.switch :action="route('admin.categories.toggle', [$c, 'featured'])" :on="$c->featured" :label="'Featured: '.$c->name" /></td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $c) }}"
                                      onsubmit="return confirm(@js('Delete "'.$c->name.'"? This cannot be undone.'))">
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

@endsection
