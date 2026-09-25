@extends('admin.layouts.app')
@section('title', 'Product reviews')
@section('crumbs')
    Product reviews
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        @if ($summary['total'] === 0)
            No reviews yet.
        @else
            {{ number_format($summary['total']).' '.Str::plural('review', $summary['total']).' · average rating '.number_format($summary['average'], 2).' / 5 · '.$summary['hidden'].' hidden' }}
        @endif
    </p>
</div>

<form method="GET" action="{{ route('admin.reviews.index') }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="Product or customer name">

    <label class="sr-only" for="filter-rating">Rating</label>
    <select id="filter-rating" name="rating" class="select">
        <option value="">Any rating</option>
        @foreach ([5, 4, 3, 2, 1] as $r)
            <option value="{{ $r }}" @selected($filters['rating'] === $r)>{{ $r }} / 5</option>
        @endforeach
    </select>

    <label class="sr-only" for="filter-status">Visibility</label>
    <select id="filter-status" name="status" class="select">
        <option value="">Visible and hidden</option>
        <option value="visible" @selected($filters['status'] === 'visible')>Visible only</option>
        <option value="hidden" @selected($filters['status'] === 'hidden')>Hidden only</option>
    </select>

    <label class="sr-only" for="filter-sort">Sort</label>
    <select id="filter-sort" name="sort" class="select">
        @foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'lowest' => 'Lowest rating', 'highest' => 'Highest rating'] as $value => $text)
            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $text }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filtered)
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-link">Clear filters</a>
    @endif
</form>

<div class="card">
    @if ($reviews->isEmpty())
        <div class="empty">
            @if ($filtered)
                <strong>Nothing matches</strong>
                Try fewer filters, or <a href="{{ route('admin.reviews.index') }}" style="color:var(--tangerine)">see every review</a>.
            @else
                <strong>No reviews yet</strong>
                Reviews appear here when customers rate products they received.
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customer</th>
                        <th class="right">Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="wrap">Visible</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($reviews as $review)
                    <tr>
                        <td>
                            <div class="product-cell">
                                @if ($review->product->thumbnail)
                                    <img src="{{ $review->product->thumbnail->url() }}" alt="" class="thumb" loading="lazy">
                                @else
                                    <span class="thumb thumb-missing" title="No image"></span>
                                @endif
                                <div style="min-width:0">
                                    <a href="{{ route('admin.products.edit', $review->product) }}" class="product-name">{{ $review->product->name }}</a>
                                </div>
                            </div>
                        </td>
                        <td>{{ $review->user?->name ?? 'Deleted customer' }}</td>
                        <td class="right num" style="white-space:nowrap">
                            <span aria-hidden="true">{{ $review->rating }} / 5</span>
                            <span class="sr-only">Rated {{ $review->rating }} out of 5</span>
                        </td>
                        <td class="review-comment">
                            @if ($review->comment === null || $review->comment === '')
                                <span class="faint">No comment</span>
                            @elseif (mb_strlen($review->comment) <= 140)
                                {{ $review->comment }}
                            @else
                                <details>
                                    <summary>{{ Str::limit($review->comment, 140) }}</summary>
                                    <p>{{ $review->comment }}</p>
                                </details>
                            @endif
                        </td>
                        <td class="muted" style="white-space:nowrap">{{ $review->created_at->timezone('Asia/Dhaka')->format('j M Y') }}</td>
                        <td>
                            <x-admin.switch :action="route('admin.reviews.toggle', [$review, 'status'])" :on="$review->status" :label="'Visible on the storefront: review of '.$review->product->name" />
                        </td>
                        <td>
                            <div class="row-actions">
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                                      onsubmit="return confirm(@js('Delete this review of "'.$review->product->name.'"? This cannot be undone. To keep it but take it off the storefront, switch it off instead.'))">
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

@if ($reviews->hasPages())
    <div style="margin-top:16px">{{ $reviews->links() }}</div>
@endif

@endsection
