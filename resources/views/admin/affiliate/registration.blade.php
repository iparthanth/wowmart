@extends('admin.layouts.app')
@section('title', 'Affiliate Registration Form')
@section('crumbs')
    Affiliate System / Registration Form
@endsection

@section('content')

@php
    $fields = json_decode($settings['affiliate_registration_fields'] ?: '[]', true) ?? [];
    $types = ['text' => 'Text', 'number' => 'Number', 'email' => 'Email', 'tel' => 'Phone', 'textarea' => 'Textarea'];
@endphp

<form method="POST" action="{{ route('admin.affiliate.registration.save') }}" class="card" style="max-width:860px">
    @csrf
    <div class="card-head"><h2>Affiliate registration form</h2></div>
    <div class="card-body">
        <div class="field" style="max-width:320px">
            <label for="reg-enabled">Registration form</label>
            <select id="reg-enabled" name="affiliate_registration_enabled" class="input">
                <option value="1" @selected($settings['affiliate_registration_enabled'] === '1')>Shown to customers</option>
                <option value="0" @selected($settings['affiliate_registration_enabled'] === '0')>Hidden</option>
            </select>
        </div>
        <p class="muted">Besides name, email and phone, the signup form can ask for extra details. Add a row per question.</p>

        <div id="field-rows">
            @foreach ($fields as $i => $field)
                <div class="field-row" style="margin-bottom:8px">
                    <x-admin.field name="fields[{{ $i }}][label]" label="Field label" :value="(string) ($field['label'] ?? '')" required maxlength="120" />
                    <div class="field">
                        <label>Field type</label>
                        <select name="fields[{{ $i }}][type]" class="input">
                            @foreach ($types as $value => $text)
                                <option value="{{ $value }}" @selected(($field['type'] ?? '') === $value)>{{ $text }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-ghost btn-sm" id="add-field"
                data-template="{{ trim(view('admin.affiliate.registration-row', ['i' => 'ROW', 'types' => $types])->render()) }}">
            Add field
        </button>

        <div style="margin-top:16px">
            <button type="submit" class="btn btn-primary">Save registration form</button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.getElementById('add-field').addEventListener('click', function () {
        var holder = document.getElementById('field-rows');
        var wrap = document.createElement('div');
        wrap.innerHTML = this.dataset.template.replace(/ROW/g, String(holder.children.length));
        holder.appendChild(wrap.firstElementChild);
    });
</script>
@endpush
