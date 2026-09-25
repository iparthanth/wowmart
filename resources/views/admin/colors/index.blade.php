@extends('admin.layouts.app')
@section('title', 'Colours')
@section('crumbs')
    Colours
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($colors->isEmpty())
                <div class="empty">
                    <strong>No colours yet</strong>
                    Add the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Colour</th>
                                <th>Code</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($colors as $c)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        {{-- Only a real hex code goes into the style attribute; rows written elsewhere are not validated. --}}
                                        @if (preg_match('/^#[0-9A-Fa-f]{6}$/', $c->code))
                                            <span class="swatch" style="background: {{ $c->code }}"></span>
                                        @else
                                            <span class="swatch thumb-missing" title="Not a hex code"></span>
                                        @endif
                                        <a href="{{ route('admin.colors.edit', $c) }}" class="product-name">{{ $c->name }}</a>
                                    </div>
                                </td>
                                <td class="mono">{{ $c->code }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.colors.edit', $c) }}" class="btn btn-ghost btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.colors.destroy', $c) }}"
                                              onsubmit="return confirm(@js('Delete the colour "'.$c->name.'"? This cannot be undone.'))">
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

        @if ($colors->hasPages())
            <div style="margin-top:16px">{{ $colors->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.colors.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add a colour</h2></div>
        <div class="card-body">
            @include('admin.colors.fields', ['color' => $newColor])
            <button type="submit" class="btn btn-primary btn-block">Add colour</button>
        </div>
    </form>
</div>

@endsection
