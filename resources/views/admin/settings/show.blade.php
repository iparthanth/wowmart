@extends('admin.layouts.app')
@section('title', $screenLabel)
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / {{ $screenLabel }}
@endsection

@php
    // After a failed save show what was typed; otherwise show what is stored.
    $val = fn (string $key) => old($key, $values[$key]);
    $checked = function (string $key) use ($values) {
        $v = old($key, $values[$key]);

        return in_array($v, [true, 1, '1', 'on'], true);
    };
@endphp

@section('content')

<div class="split">
    <form method="POST" action="{{ route('admin.settings.update', $screen) }}" class="card">
        @csrf
        @method('PUT')
        <div class="card-head"><h2>{{ $screenLabel }}</h2></div>
        <div class="card-body">
            @foreach ($fields as $key => $field)
                @if ($field['type'] === 'boolean')
                    {{-- The hidden 0 makes an unchecked box still save "off". --}}
                    <div class="field">
                        <label class="check">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input id="f-{{ $key }}" type="checkbox" name="{{ $key }}" value="1" @checked($checked($key))>
                            {{ $field['label'] }}
                        </label>
                        @if (($errors->has($key)))<span class="error">{{ $errors->first($key) }}</span>@endif
                        @if (! empty($field['hint']))<span class="hint">{{ $field['hint'] }}</span>@endif
                    </div>
                @elseif ($field['type'] === 'select')
                    <x-admin.field name="{{ $key }}" label="{{ $field['label'] }}" :hint="$field['hint'] ?? null">
                        <select id="f-{{ $key }}" name="{{ $key }}" class="input">
                            <option value="">- choose -</option>
                            @foreach ($field['options'] as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected((string) $val($key) === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                @elseif ($field['type'] === 'textarea')
                    <x-admin.field name="{{ $key }}" label="{{ $field['label'] }}" :hint="$field['hint'] ?? null">
                        <textarea id="f-{{ $key }}" name="{{ $key }}" class="input" rows="3">{{ $val($key) }}</textarea>
                    </x-admin.field>
                @elseif ($field['type'] === 'secret')
                    <x-admin.field name="{{ $key }}" label="{{ $field['label'] }}" type="password"
                                   :hint="$isSet[$key] ? 'A value is saved - leave blank to keep it.' : 'Stored encrypted once saved.'" />
                @else
                    <x-admin.field name="{{ $key }}" label="{{ $field['label'] }}" :type="$field['type'] === 'number' ? 'number' : 'text'"
                                   :value="$val($key)" :hint="$field['hint'] ?? null" />
                @endif
            @endforeach

            <div class="row-actions" style="justify-content:flex-start">
                <button type="submit" class="btn btn-primary">Save settings</button>
            </div>
        </div>
    </form>

    @if ($screen === 'smtp')
        <form method="POST" action="{{ route('admin.settings.test-mail') }}" class="card">
            @csrf
            <div class="card-head"><h2>Send a test email</h2></div>
            <div class="card-body">
                <x-admin.field name="test_email" label="Send to" type="email" :value="old('test_email')"
                               hint="Uses the SMTP values on this screen, saved or just edited." />
                <button type="submit" class="btn btn-primary btn-block">Send test email</button>
            </div>
        </form>
    @endif
</div>

@endsection
