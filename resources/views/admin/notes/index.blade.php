@use('App\Models\Note')
@extends('admin.layouts.app')
@section('title', 'Notes')
@section('crumbs')
    Notes
@endsection

@section('content')

<div class="page-actions">
    <nav class="row-actions" style="justify-content:flex-start" aria-label="Filter by type">
        <a href="{{ route('admin.notes.index') }}" class="btn btn-sm {{ $type === null ? 'btn-primary' : 'btn-ghost' }}" @if ($type === null) aria-current="page" @endif>All notes</a>
        @foreach (Note::TYPES as $value => $label)
            <a href="{{ route('admin.notes.index', ['type' => $value]) }}" class="btn btn-sm {{ $type === $value ? 'btn-primary' : 'btn-ghost' }}"
               @if ($type === $value) aria-current="page" @endif>{{ $label }} notes</a>
        @endforeach
    </nav>
    <a href="{{ route('admin.notes.create') }}" class="btn btn-primary">Add new note</a>
</div>

<div class="card">
    @if ($notes->isEmpty())
        <div class="empty">
            <strong>No {{ $type ? Note::TYPES[$type] : '' }} notes yet</strong>
            Add one to reuse the same refund or warranty terms across products.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Note</th>
                        <th class="right">Products</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($notes as $n)
                    <tr>
                        <td><span class="pill {{ $n->type === 'refund' ? 'pill-info' : 'pill-mute' }}">{{ $n->typeLabel() }}</span></td>
                        <td class="note-text"><a href="{{ route('admin.notes.edit', $n) }}">{{ Str::limit($n->description, 160) }}</a></td>
                        <td class="right num">{{ number_format($n->productsCount()) }}</td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('admin.notes.edit', $n) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.notes.destroy', $n) }}"
                                      onsubmit="return confirm(@js('Delete this '.strtolower($n->typeLabel()).' note? This cannot be undone.'))">
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

@if ($notes->hasPages())
    <div style="margin-top:16px">{{ $notes->links() }}</div>
@endif

@endsection
