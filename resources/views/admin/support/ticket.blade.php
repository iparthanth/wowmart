@extends('admin.layouts.app')
@section('title', $ticket->code)
@section('crumbs')
    <a href="{{ route('admin.support.tickets') }}">Tickets</a> / {{ $ticket->code }}
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head">
                <h2>{{ $ticket->subject }}</h2>
                @if ($ticket->status === 'open')
                    <span class="pill pill-info">open</span>
                @else
                    <span class="pill pill-mute">closed</span>
                @endif
            </div>
            <div class="ticket-thread">
                @foreach ($ticket->replies as $reply)
                    <div class="ticket-bubble {{ $reply->user?->isAdmin() ? 'ticket-bubble-admin' : '' }}">
                        <div class="ticket-bubble-head">
                            <strong>{{ $reply->user?->name ?? 'Deleted user' }}</strong>
                            @if ($reply->user?->isAdmin())
                                <span class="pill pill-ok">staff</span>
                            @endif
                            <span class="muted">{{ $reply->created_at->format('j M Y, g:i a') }}</span>
                        </div>
                        <p style="margin:6px 0 0;white-space:pre-wrap">{{ $reply->message }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($ticket->status === 'open')
            <form method="POST" action="{{ route('admin.support.tickets.reply', $ticket) }}" class="card" style="margin-top:16px">
                @csrf
                <div class="card-body">
                    <div class="field">
                        <label for="r-message">Reply as WowMart Admin</label>
                        <textarea id="r-message" name="message" rows="4" class="input" required>{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post reply</button>
                </div>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="card-head"><h2>Details</h2></div>
        <div class="card-body">
            <p><strong>Reference</strong><br><span class="mono">{{ $ticket->code }}</span></p>
            <p><strong>Customer</strong><br>{{ $ticket->user?->name }}<br><span class="muted">{{ $ticket->user?->email }}</span></p>
            <p><strong>Opened</strong><br>{{ $ticket->created_at->format('j M Y, g:i a') }}</p>
            <form method="POST" action="{{ route('admin.support.tickets.status', $ticket) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $ticket->status === 'open' ? 'closed' : 'open' }}">
                <button type="submit" class="btn {{ $ticket->status === 'open' ? 'btn-ghost' : 'btn-primary' }} btn-block">
                    {{ $ticket->status === 'open' ? 'Close ticket' : 'Reopen ticket' }}
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
