@extends('admin.layouts.app')
@section('title', 'Subscribers')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Marketing / Subscribers
@endsection

@section('content')

<div class="card">
    @if ($subscribers->isEmpty())
        <div class="empty">
            <strong>No subscribers yet</strong>
            Footer opt-ins from the storefront land here.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($subscribers as $s)
                    <tr>
                        <td>{{ $s->email }}</td>
                        <td>
                            @if ($s->status)
                                <span class="pill pill-info">subscribed</span>
                            @else
                                <span class="pill pill-mute">unsubscribed</span>
                            @endif
                        </td>
                        <td>{{ $s->created_at->format('j M Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.marketing.subscribers.destroy', $s) }}"
                                  onsubmit="return confirm(@js('Remove '.$s->email.' from the list?'))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm link-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($subscribers->hasPages())
    <div style="margin-top:16px">{{ $subscribers->links() }}</div>
@endif

@endsection
