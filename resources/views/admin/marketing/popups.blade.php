@extends('admin.layouts.app')
@section('title', 'Dynamic Pop-up')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Marketing / Dynamic Pop-up
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($popups->isEmpty())
                <div class="empty">
                    <strong>No pop-ups yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pop-up</th>
                                <th>Shows on</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($popups as $p)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.marketing.popups.edit', $p) }}" class="product-name">{{ $p->title }}</a>
                                        @if (! $p->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @elseif ($p->once_per_session)
                                            <span class="pill pill-info">once per session</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="pill">{{ ucfirst($p->page) }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.marketing.popups.edit', $p) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.marketing.popups.destroy', $p) }}"
                                              onsubmit="return confirm(@js('Delete the pop-up "'.$p->title.'"? This cannot be undone.'))">
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

        @if ($popups->hasPages())
            <div style="margin-top:16px">{{ $popups->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.marketing.popups.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a pop-up</h2></div>
        <div class="card-body">
            @include('admin.marketing.popup-fields', ['popup' => $newPopup])
            <button type="submit" class="btn btn-primary btn-block">Add pop-up</button>
        </div>
    </form>
</div>

@endsection
