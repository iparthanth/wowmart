@extends('admin.layouts.app')
@section('title', 'Product Conversations')
@section('crumbs')
    Support / Product Conversations
@endsection

@section('content')

<div class="card">
    <div class="card-head" style="flex-wrap:wrap;gap:8px">
        <h2>Product conversations</h2>
        <div class="row-actions">
            @foreach (['' => 'All', 'open' => 'Open', 'closed' => 'Closed'] as $value => $text)
                <a href="{{ route('admin.support.conversations', $value === '' ? [] : ['status' => $value]) }}"
                   class="btn btn-sm {{ request('status', '') === $value ? 'btn-primary' : 'btn-ghost' }}">{{ $text }}</a>
            @endforeach
        </div>
    </div>
    @if ($conversations->isEmpty())
        <div class="empty">
            <strong>No conversations {{ request('status') ? 'are '.request('status') : 'yet' }}</strong>
            Buyer-seller chats about a product appear here so the panel can keep an eye on them.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Conversation</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Seller</th>
                        <th>Status</th>
                        <th>Opened</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($conversations as $conversation)
                    <tr>
                        <td>
                            <a href="{{ route('admin.support.conversations.show', $conversation) }}" class="product-name">{{ $conversation->title }}</a>
                            <div class="product-meta muted mono">#{{ $conversation->id }}</div>
                        </td>
                        <td>{{ $conversation->product?->name }}</td>
                        <td>{{ $conversation->customer?->name }}</td>
                        <td>{{ $conversation->seller?->name }}</td>
                        <td>
                            @if ($conversation->status === 'open')
                                <span class="pill pill-info">open</span>
                            @else
                                <span class="pill pill-mute">closed</span>
                            @endif
                        </td>
                        <td class="muted">{{ $conversation->created_at->format('j M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.support.conversations.show', $conversation) }}" class="btn btn-ghost btn-sm">Open</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($conversations->hasPages())
    <div style="margin-top:16px">{{ $conversations->links() }}</div>
@endif

@endsection
