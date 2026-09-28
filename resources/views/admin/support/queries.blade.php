@extends('admin.layouts.app')
@section('title', 'Product Queries')
@section('crumbs')
    Support / Product Queries
@endsection

@section('content')

<div class="card">
    <div class="card-head" style="flex-wrap:wrap;gap:8px">
        <h2>Product questions</h2>
        <div class="row-actions">
            @foreach (['' => 'All', 'open' => 'Open', 'answered' => 'Answered'] as $value => $text)
                <a href="{{ route('admin.support.queries', $value === '' ? [] : ['status' => $value]) }}"
                   class="btn btn-sm {{ request('status', '') === $value ? 'btn-primary' : 'btn-ghost' }}">{{ $text }}</a>
            @endforeach
        </div>
    </div>
    @if ($queries->isEmpty())
        <div class="empty">
            <strong>No questions {{ request('status') ? 'are '.request('status') : 'yet' }}</strong>
            Questions customers ask on products land here.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Product</th>
                        <th>Question</th>
                        <th>Reply</th>
                        <th>Status</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($queries as $query)
                    <tr>
                        <td>{{ $query->user?->name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($query->product?->name, 36) }}</td>
                        <td style="max-width:260px">{{ \Illuminate\Support\Str::limit($query->question, 90) }}
                            <div class="product-meta muted">{{ $query->created_at->format('j M Y') }}</div>
                        </td>
                        <td style="max-width:240px">{{ $query->reply ? \Illuminate\Support\Str::limit($query->reply, 70) : '—' }}</td>
                        <td>
                            @if ($query->status === 'answered')
                                <span class="pill pill-ok">answered</span>
                            @else
                                <span class="pill pill-info">open</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <form method="POST" action="{{ route('admin.support.queries.reply', $query) }}" class="inline-form">
                                    @csrf
                                    <input type="hidden" name="reply" value="Yes, this product is in stock and ships within two working days.">
                                    <button type="submit" class="btn btn-ghost btn-sm" @if ($query->status === 'answered') disabled @endif>Answer</button>
                                </form>
                                <form method="POST" action="{{ route('admin.support.queries.destroy', $query) }}"
                                      onsubmit="return confirm('Delete this question? This cannot be undone.')">
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

@if ($queries->hasPages())
    <div style="margin-top:16px">{{ $queries->links() }}</div>
@endif

@endsection
