@extends('admin.layouts.app')
@section('title', 'Email Templates')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Marketing / Email Templates
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head" style="flex-wrap:wrap;gap:8px">
                <h2>Email templates</h2>
                <div class="row-actions">
                    <a href="{{ route('admin.marketing.templates') }}"
                       class="btn btn-sm {{ $receiver ? 'btn-ghost' : 'btn-primary' }}">All</a>
                    @foreach ($receivers as $r)
                        <a href="{{ route('admin.marketing.templates', ['receiver' => $r]) }}"
                           class="btn btn-sm {{ $receiver === $r ? 'btn-primary' : 'btn-ghost' }}">{{ ucfirst($r) }}</a>
                    @endforeach
                </div>
            </div>
            @if ($templates->isEmpty())
                <div class="empty">
                    <strong>{{ $receiver ? 'No '.ucfirst((string) $receiver).' templates' : 'No templates yet' }}</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Template</th>
                                <th>Receiver</th>
                                <th>Subject</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($templates as $t)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.marketing.templates.edit', $t) }}" class="product-name">{{ $t->identifier }}</a>
                                        @if (! $t->status)
                                            <span class="pill pill-mute">inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="pill">{{ ucfirst($t->receiver) }}</span></td>
                                <td>{{ \Illuminate\Support\Str::limit($t->subject, 40) }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.marketing.templates.edit', $t) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.marketing.templates.destroy', $t) }}"
                                              onsubmit="return confirm(@js('Delete the template "'.$t->identifier.'"? This cannot be undone.'))">
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

        @if ($templates->hasPages())
            <div style="margin-top:16px">{{ $templates->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.marketing.templates.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a template</h2></div>
        <div class="card-body">
            @include('admin.marketing.template-fields', ['template' => $newTemplate, 'receivers' => $receivers])
            <button type="submit" class="btn btn-primary btn-block">Add template</button>
        </div>
    </form>
</div>

@endsection
