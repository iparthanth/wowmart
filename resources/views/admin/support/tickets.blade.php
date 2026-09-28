@extends('admin.layouts.app')
@section('title', 'Support Tickets')
@section('crumbs')
    Support / Tickets
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head" style="flex-wrap:wrap;gap:8px">
                <h2>Tickets</h2>
                <div class="row-actions">
                    @foreach (['' => 'All', 'open' => 'Open', 'closed' => 'Closed'] as $value => $text)
                        <a href="{{ route('admin.support.tickets', $value === '' ? [] : ['status' => $value]) }}"
                           class="btn btn-sm {{ request('status', '') === $value ? 'btn-primary' : 'btn-ghost' }}">{{ $text }}</a>
                    @endforeach
                </div>
            </div>
            @if ($tickets->isEmpty())
                <div class="empty">
                    <strong>No tickets {{ request('status') ? 'are '.request('status') : 'yet' }}</strong>
                    Customer tickets land here; open one by hand with the form beside the list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Opened</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.support.tickets.show', $ticket) }}" class="product-name">{{ $ticket->subject }}</a>
                                    <div class="product-meta muted mono">{{ $ticket->code }}</div>
                                </td>
                                <td>{{ $ticket->user?->name }}</td>
                                <td>
                                    @if ($ticket->status === 'open')
                                        <span class="pill pill-info">open</span>
                                    @else
                                        <span class="pill pill-mute">closed</span>
                                    @endif
                                </td>
                                <td class="muted">{{ $ticket->created_at->format('j M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.support.tickets.show', $ticket) }}" class="btn btn-ghost btn-sm">Open</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($tickets->hasPages())
            <div style="margin-top:16px">{{ $tickets->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.support.tickets.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Open a ticket</h2></div>
        <div class="card-body">
            @php
                $fresh = ! session()->hasOldInput();
                $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
            @endphp
            <div class="field">
                <label for="t-user">Customer</label>
                <select id="t-user" name="user_id" class="input" required>
                    <option value="">Choose a customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected($v('user_id') == $customer->id)>{{ $customer->name }} ({{ $customer->email }})</option>
                    @endforeach
                </select>
            </div>
            <x-admin.field name="subject" label="Subject" :value="$v('subject')" required maxlength="180" />
            <div class="field">
                <label for="t-message">Message</label>
                <textarea id="t-message" name="message" rows="4" class="input" required>{{ $v('message') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Open ticket</button>
        </div>
    </form>
</div>

@endsection
