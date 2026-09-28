@extends('admin.layouts.app')
@section('title', 'Conversation #'.$conversation->id)
@section('crumbs')
    <a href="{{ route('admin.support.conversations') }}">Product Conversations</a> / #{{ $conversation->id }}
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head">
                <h2>{{ $conversation->title }}</h2>
                @if ($conversation->status === 'open')
                    <span class="pill pill-info">open</span>
                @else
                    <span class="pill pill-mute">closed</span>
                @endif
            </div>
            <div class="ticket-thread">
                @forelse ($conversation->messages as $message)
                    <div class="ticket-bubble {{ $message->user?->isAdmin() ? 'ticket-bubble-admin' : '' }}">
                        <div class="ticket-bubble-head">
                            <strong>{{ $message->user?->name ?? 'Deleted user' }}</strong>
                            @if ($message->user_id === $conversation->seller_id)
                                <span class="pill pill-warn">seller</span>
                            @elseif ($message->user_id === $conversation->customer_id)
                                <span class="pill pill-info">customer</span>
                            @elseif ($message->user?->isAdmin())
                                <span class="pill pill-ok">staff</span>
                            @endif
                            <span class="muted">{{ $message->created_at->format('j M Y, g:i a') }}</span>
                        </div>
                        <p style="margin:6px 0 0;white-space:pre-wrap">{{ $message->message }}</p>
                    </div>
                @empty
                    <div class="empty"><strong>No messages yet</strong> The thread is quiet so far.</div>
                @endforelse
            </div>
        </div>

        @if ($conversation->status === 'open')
            <form method="POST" action="{{ route('admin.support.conversations.reply', $conversation) }}" class="card" style="margin-top:16px">
                @csrf
                <div class="card-body">
                    <div class="field">
                        <label for="r-message">Join as WowMart Admin</label>
                        <textarea id="r-message" name="message" rows="4" class="input" required>{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post message</button>
                </div>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="card-head"><h2>Details</h2></div>
        <div class="card-body">
            @if ($conversation->product)
                <p><strong>Product</strong><br>{{ $conversation->product->name }}</p>
            @endif
            <p><strong>Customer</strong><br>{{ $conversation->customer?->name }}<br><span class="muted">{{ $conversation->customer?->email }}</span></p>
            <p><strong>Seller</strong><br>{{ $conversation->seller?->name }}<br><span class="muted">{{ $conversation->seller?->email }}</span></p>
            <p><strong>Opened</strong><br>{{ $conversation->created_at->format('j M Y, g:i a') }}</p>
            <form method="POST" action="{{ route('admin.support.conversations.status', $conversation) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $conversation->status === 'open' ? 'closed' : 'open' }}">
                <button type="submit" class="btn {{ $conversation->status === 'open' ? 'btn-ghost' : 'btn-primary' }} btn-block">
                    {{ $conversation->status === 'open' ? 'Close conversation' : 'Reopen conversation' }}
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
