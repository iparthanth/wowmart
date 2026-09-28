@extends('admin.layouts.app')
@section('title', 'Languages')
@section('crumbs')
    Setup & Configurations / Languages
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($languages->isEmpty())
                <div class="empty">
                    <strong>No languages yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Language</th>
                                <th>Code</th>
                                <th>RTL</th>
                                <th>Status</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($languages as $language)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <a href="{{ route('admin.languages.edit', $language) }}" class="product-name">{{ $language->name }}</a>
                                        @if ($language->code === $defaultCode)
                                            <span class="pill pill-ok">default</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="mono">{{ $language->code }}</td>
                                <td>{{ $language->rtl ? 'Yes' : '—' }}</td>
                                <td>
                                    @if ($language->status)
                                        <span class="pill pill-ok">enabled</span>
                                    @else
                                        <span class="pill pill-mute">disabled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @if ($language->code !== $defaultCode)
                                            <form method="POST" action="{{ route('admin.languages.default', $language) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-sm">Make default</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.languages.edit', $language) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.languages.destroy', $language) }}"
                                              onsubmit="return confirm(@js('Delete the language "'.$language->name.'"? This cannot be undone.'))">
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

        @if ($languages->hasPages())
            <div style="margin-top:16px">{{ $languages->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.languages.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a language</h2></div>
        <div class="card-body">
            @include('admin.languages.fields', ['language' => $newLanguage])
            <button type="submit" class="btn btn-primary btn-block">Add language</button>
        </div>
    </form>
</div>

@endsection
