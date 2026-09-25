@extends('admin.layouts.app')
@section('title', $attribute->name)
@section('crumbs')
    <a href="{{ route('admin.attributes.index') }}">Attributes</a> / Edit
@endsection

@section('content')

<div class="split">
    <div class="card">
        <div class="card-head">
            <h2>Values of {{ $attribute->name }}</h2>
            <span class="faint">{{ $values->count() }} {{ Str::plural('value', $values->count()) }}</span>
        </div>
        @if ($values->isEmpty())
            <div class="empty">
                <strong>No values yet</strong>
                Add the choices shoppers pick from, like Small, Medium and Large.
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Value</th>
                            <th class="tight"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($values as $val)
                        @php
                            // Each rename form has its own error bag (AttributeValueRequest), so only the failed row shows what was typed.
                            $bag = $errors->getBag('value'.$val->id);
                        @endphp
                        <tr>
                            <td>
                                <form method="POST" action="{{ route('admin.attributes.values.update', $val) }}" class="value-row">
                                    @csrf
                                    @method('PUT')
                                    <label class="sr-only" for="value-{{ $val->id }}">Rename {{ $val->value }}</label>
                                    <input id="value-{{ $val->id }}" name="value" class="input" required maxlength="100"
                                           value="{{ $bag->any() ? old('value') : $val->value }}"
                                           @if ($bag->any()) aria-invalid="true" aria-describedby="value-{{ $val->id }}-error" @endif>
                                    <button type="submit" class="btn btn-ghost btn-sm">Rename</button>
                                </form>
                                @if ($bag->any())
                                    <span class="error" id="value-{{ $val->id }}-error">{{ $bag->first('value') }}</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.attributes.values.destroy', $val) }}"
                                      onsubmit="return confirm(@js('Delete the value "'.$val->value.'"?'))">
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

    <div>
        <form method="POST" action="{{ route('admin.attributes.values.store', $attribute) }}" class="card">
            @csrf
            <div class="card-head"><h2>Add a value</h2></div>
            <div class="card-body">
                <x-admin.field name="value" label="Value" :value="$errors->has('value') ? old('value') : null" required maxlength="100"
                               hint="Each value is one choice on the product form." />
                <button type="submit" class="btn btn-primary btn-block">Add value</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.attributes.update', $attribute) }}" class="card" style="margin-top:16px">
            @csrf
            @method('PUT')
            <div class="card-head"><h2>Rename attribute</h2></div>
            <div class="card-body">
                <x-admin.field name="name" label="Name" :value="$errors->has('name') ? old('name') : $attribute->name" required maxlength="60" />
                <button type="submit" class="btn btn-ghost btn-block">Save name</button>
            </div>
        </form>

        <p style="margin-top:16px"><a href="{{ route('admin.attributes.index') }}" class="btn btn-link">Back to attributes</a></p>
    </div>
</div>

@endsection
