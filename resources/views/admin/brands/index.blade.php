@extends('admin.layouts.app')
@section('title', 'Brands')
@section('crumbs')
    Brands
@endsection

@section('content')

<div class="split">
    <div>
        <form method="GET" action="{{ route('admin.brands.index') }}" class="card filters" role="search">
            <label class="sr-only" for="q">Search brands</label>
            <input id="q" type="search" name="search" value="{{ $search }}" class="input filter-search" placeholder="Brand name">
            <button type="submit" class="btn btn-ghost">Search</button>
            @if ($search !== '')
                <a href="{{ route('admin.brands.index') }}" class="btn btn-link">Clear search</a>
            @endif
        </form>

        <div class="card">
            @if ($brands->isEmpty())
                <div class="empty">
                    @if ($search !== '')
                        <strong>No brand matches "{{ $search }}"</strong>
                        Check the spelling, or add it with the form beside this list.
                    @else
                        <strong>No brands yet</strong>
                        Add the first one with the form beside this list.
                    @endif
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th class="right">Products</th>
                                <th class="wrap">Top brand</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($brands as $b)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        @if ($b->logoFile)
                                            <img src="{{ $b->logoFile->url() }}" alt="" class="thumb" loading="lazy">
                                        @else
                                            <span class="thumb thumb-missing" title="No logo"></span>
                                        @endif
                                        <div style="min-width:0">
                                            <a href="{{ route('admin.brands.edit', $b) }}" class="product-name">{{ $b->name }}</a>
                                            <div class="product-meta mono">{{ $b->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="right num">{{ number_format($b->products_count) }}</td>
                                <td><x-admin.switch :action="route('admin.brands.toggle', [$b, 'top'])" :on="$b->top" :label="'Top brand: '.$b->name" /></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.brands.edit', $b) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.brands.destroy', $b) }}"
                                              onsubmit="return confirm(@js('Delete the brand "'.$b->name.'"? This cannot be undone.'))">
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

        @if ($brands->hasPages())
            <div style="margin-top:16px">{{ $brands->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.brands.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a brand</h2></div>
        <div class="card-body">
            @include('admin.brands.fields', ['brand' => $newBrand])
            <button type="submit" class="btn btn-primary btn-block">Add brand</button>
        </div>
    </form>
</div>

@endsection
