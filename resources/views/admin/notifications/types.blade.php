@extends('admin.layouts.app')
@section('title', 'Notification Types')
@section('crumbs')
    Notification / Types
@endsection

@section('content')

<div class="split" style="align-items:start">
    <div class="card">
        <div class="card-head" style="flex-wrap:wrap;gap:8px">
            <h2>Notification types</h2>
            <form method="GET" action="{{ route('admin.notifications.types') }}" class="row-actions">
                <input type="text" name="sort_search" class="input" style="width:200px" placeholder="Search by name" value="{{ request('sort_search') }}">
                <button type="submit" class="btn btn-ghost btn-sm">Search</button>
            </form>
        </div>
        @if ($types->isEmpty())
            <div class="empty"><strong>No notification types yet</strong> Types drive the icons and texts shown on the storefront.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Type</th>
                            <th>Default text</th>
                            <th>Status</th>
                            <th class="tight"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($types as $type)
                        <tr>
                            <td>
                                @if ($type->imageUpload)
                                    <img src="{{ $type->imageUpload->url() }}" alt="" style="width:36px;height:36px;object-fit:cover">
                                @else
                                    <span class="faint">—</span>
                                @endif
                            </td>
                            <td>{{ $type->name }}</td>
                            <td class="muted">{{ $type->default_text }}</td>
                            <td>
                                @if ($type->status)
                                    <span class="pill pill-ok">on</span>
                                @else
                                    <span class="pill pill-mute">off</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.notifications.types.edit', $type) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.notifications.types.destroy', $type) }}" style="display:inline"
                                      onsubmit="return confirm(@js('Delete type '.$type->name.'?'))">
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

    <form method="POST" action="{{ route('admin.notifications.types.store') }}" class="card" style="max-width:420px">
        @csrf
        <div class="card-head"><h3>Add new notification type</h3></div>
        <div class="card-body">
            <x-admin.field name="name" label="Type" value="{{ old('name') }}" required maxlength="60" />
            <div class="field">
                <span class="field-label">Image (36x36)</span>
                <x-admin.media-picker name="image" :images="$images" :selected="old('image')" />
            </div>
            <x-admin.field name="default_text" label="Default text" value="{{ old('default_text') }}" required maxlength="80" />
            <label class="check">
                <input type="hidden" name="status" value="0">
                <input type="checkbox" name="status" value="1" @checked(old('status', '1') === '1')>
                Active
            </label>
            <button type="submit" class="btn btn-primary" style="margin-top:12px">Add type</button>
        </div>
    </form>
</div>

@endsection
