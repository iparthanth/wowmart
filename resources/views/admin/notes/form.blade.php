{{-- The note form, for create and edit. The type is chosen once and shown read-only afterwards. --}}
@use('App\Models\Note')
@extends('admin.layouts.app')
@section('title', $note->exists ? 'Edit note' : 'Add new note')
@section('crumbs')
    <a href="{{ route('admin.notes.index') }}">Notes</a> / {{ $note->exists ? 'Edit' : 'New' }}
@endsection

@section('content')
@php
    // After a failed save show what was typed; otherwise show what is stored.
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<form method="POST" action="{{ $note->exists ? route('admin.notes.update', $note) : route('admin.notes.store') }}" class="card" style="max-width:760px">
    @csrf
    @if ($note->exists)
        @method('PUT')
    @endif
    <div class="card-head">
        <h2>{{ $note->exists ? $note->typeLabel().' note' : 'New note' }}</h2>
        @if ($note->exists)
            <span class="faint">Used by {{ number_format($note->productsCount()) }} {{ Str::plural('product', $note->productsCount()) }}</span>
        @endif
    </div>
    <div class="card-body">
        @if ($note->exists)
            <p class="hint" style="margin-top:0">A note keeps its type. For {{ $note->type === 'refund' ? 'warranty' : 'refund' }} terms, add a new note.</p>
        @else
            <fieldset class="field">
                <legend class="field-label">Type</legend>
                <div class="checks">
                    @foreach (Note::TYPES as $value => $label)
                        <label class="check"><input type="radio" name="type" value="{{ $value }}" required @checked($v('type', 'refund') === $value)> {{ $label }}</label>
                    @endforeach
                </div>
                @error('type')<span class="error">{{ $message }}</span>@enderror
            </fieldset>
        @endif

        <x-admin.field name="description" label="Note" hint="Shown on every product that uses it. Up to 5,000 characters.">
            <textarea id="f-description" name="description" class="textarea" rows="8" required maxlength="5000">{{ $v('description', $note->description) }}</textarea>
        </x-admin.field>

        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">{{ $note->exists ? 'Save changes' : 'Add note' }}</button>
            <a href="{{ route('admin.notes.index') }}" class="btn btn-link">Back to notes</a>
        </div>
    </div>
</form>

@endsection
