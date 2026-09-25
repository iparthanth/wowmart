@extends('admin.layouts.app')
@section('title', 'Attributes')
@section('crumbs')
    Attributes
@endsection

@section('content')

<div class="split">
    <div>
        <form method="GET" action="{{ route('admin.attributes.index') }}" class="card filters" role="search">
            <label class="sr-only" for="q">Search attributes</label>
            <input id="q" type="search" name="search" value="{{ $search }}" class="input filter-search" placeholder="Attribute name">
            <button type="submit" class="btn btn-ghost">Search</button>
            @if ($search !== '')
                <a href="{{ route('admin.attributes.index') }}" class="btn btn-link">Clear search</a>
            @endif
        </form>

        <div class="card">
            @if ($attributes->isEmpty())
                <div class="empty">
                    @if ($search !== '')
                        <strong>No attribute matches "{{ $search }}"</strong>
                        Check the spelling, or add it with the form beside this list.
                    @else
                        <strong>No attributes yet</strong>
                        Add one, like Size or Capacity, to offer product variants.
                    @endif
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Attribute</th>
                                <th>Values</th>
                                <th class="right">Count</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($attributes as $a)
                            <tr>
                                <td><a href="{{ route('admin.attributes.edit', $a) }}" class="product-name">{{ $a->name }}</a></td>
                                <td>
                                    @if ($a->values->isEmpty())
                                        <span class="faint">No values yet</span>
                                    @else
                                        <div class="value-chips">
                                            @foreach ($a->values as $val)
                                                <span class="pill pill-mute">{{ $val->value }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="right num">{{ number_format($a->values_count) }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a href="{{ route('admin.attributes.edit', $a) }}" class="btn btn-ghost btn-sm">Edit values</a>
                                        <form method="POST" action="{{ route('admin.attributes.destroy', $a) }}"
                                              onsubmit="return confirm(@js('Delete the attribute "'.$a->name.'" and its values? This cannot be undone.'))">
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

        @if ($attributes->hasPages())
            <div style="margin-top:16px">{{ $attributes->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.attributes.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Add an attribute</h2></div>
        <div class="card-body">
            <x-admin.field name="name" label="Name" :value="old('name')" required maxlength="60"
                           hint="Like Size or Capacity. You add its values next." />
            <button type="submit" class="btn btn-primary btn-block">Add attribute</button>
        </div>
    </form>
</div>

@endsection
