@extends('admin.layouts.app')
@section('title', 'Contacts')
@section('crumbs')
    Support / Contacts
@endsection

@section('content')

<div class="card">
    <div class="card-head" style="flex-wrap:wrap;gap:8px">
        <h2>Contact messages</h2>
        <div class="row-actions">
            @foreach (['' => 'All', 'open' => 'Open', 'answered' => 'Answered'] as $value => $text)
                <a href="{{ route('admin.support.contacts', $value === '' ? [] : ['status' => $value]) }}"
                   class="btn btn-sm {{ request('status', '') === $value ? 'btn-primary' : 'btn-ghost' }}">{{ $text }}</a>
            @endforeach
        </div>
    </div>
    @if ($contacts->isEmpty())
        <div class="empty">
            <strong>No messages {{ request('status') ? 'are '.request('status') : 'yet' }}</strong>
            Contact-form submissions land here.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Query</th>
                        <th>Reply</th>
                        <th>Status</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($contacts as $contact)
                    <tr>
                        <td>
                            <span class="product-name">{{ $contact->name }}</span>
                            <div class="product-meta muted">{{ $contact->email }}@if ($contact->phone) · {{ $contact->phone }} @endif</div>
                        </td>
                        <td style="max-width:280px">{{ \Illuminate\Support\Str::limit($contact->query, 90) }}
                            <div class="product-meta muted">{{ $contact->created_at->format('j M Y') }}</div>
                        </td>
                        <td style="max-width:240px">{{ $contact->reply ? \Illuminate\Support\Str::limit($contact->reply, 70) : '—' }}</td>
                        <td>
                            @if ($contact->status === 'answered')
                                <span class="pill pill-ok">answered</span>
                            @else
                                <span class="pill pill-info">open</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <form method="POST" action="{{ route('admin.support.contacts.reply', $contact) }}" class="inline-form">
                                    @csrf
                                    <input type="hidden" name="reply" value="Answered from the WowMart panel.">
                                    <button type="submit" class="btn btn-ghost btn-sm" @if ($contact->status === 'answered') disabled @endif>Mark answered</button>
                                </form>
                                <form method="POST" action="{{ route('admin.support.contacts.destroy', $contact) }}"
                                      onsubmit="return confirm('Delete this message? This cannot be undone.')">
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

@if ($contacts->hasPages())
    <div style="margin-top:16px">{{ $contacts->links() }}</div>
@endif

@endsection
