@extends('admin.layouts.app')
@section('title', 'Custom Notification History')
@section('crumbs')
    <a href="{{ route('admin.notifications.custom') }}">Custom Notification</a> / History
@endsection

@section('content')

<div class="card">
    <div class="card-head"><h2>Custom notification history</h2></div>
    @if ($notifications->isEmpty())
        <div class="empty"><strong>No announcements sent yet</strong> Every custom notification appears here after it is sent.</div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Date &amp; time</th>
                        <th>Notification</th>
                        <th>Link</th>
                        <th class="right">Recipients</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($notifications as $notification)
                    <tr>
                        <td>
                            @if ($notification->type?->imageUpload)
                                <img src="{{ $notification->type->imageUpload->url() }}" alt="" style="width:24px;height:24px;object-fit:cover;vertical-align:middle">
                            @endif
                            {{ $notification->type?->name ?? 'Deleted type' }}
                        </td>
                        <td class="muted">{{ $notification->created_at->format('j M Y, g:i a') }}</td>
                        <td>{{ $notification->content }}</td>
                        <td>
                            @if ($notification->link)
                                <a href="{{ $notification->link }}" target="_blank" rel="noopener" class="mono">{{ \Illuminate\Support\Str::limit($notification->link, 40) }}</a>
                            @else
                                <span class="faint">—</span>
                            @endif
                        </td>
                        <td class="right num">{{ $notification->users_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.notifications.custom.destroy', $notification) }}"
                                  onsubmit="return confirm(@js('Remove this notification from the history and all recipient devices?'))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm link-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($notifications->hasPages())
    <div style="margin-top:16px">{{ $notifications->links() }}</div>
@endif

@endsection
