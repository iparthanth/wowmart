@extends('admin.layouts.app')
@section('title', 'Custom Notification')
@section('crumbs')
    Notification / Custom Notification
@endsection

@section('content')

<form method="POST" action="{{ route('admin.notifications.custom.store') }}" class="card" style="max-width:760px">
    @csrf
    <div class="card-head"><h2>Send a custom notification</h2></div>
    <div class="card-body">
        <div class="field">
            <span class="field-label">Customers</span>
            <div class="table-wrap" style="max-height:280px;overflow:auto;border:1px solid var(--line-2)">
                <table class="table">
                    <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td style="width:36px">
                                <input type="checkbox" id="u{{ $customer->id }}" name="user_ids[]" value="{{ $customer->id }}"
                                       @in_array(old('user_ids', []), [$customer->id])>
                            </td>
                            <td><label for="u{{ $customer->id }}" style="margin:0">{{ $customer->name }}</label></td>
                            <td class="muted">{{ $customer->email }}</td>
                        </tr>
                    @empty
                        <tr><td class="muted">No customers yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <label for="n-type">Select type</label>
                <select id="n-type" name="notification_type_id" class="input" required>
                    <option value="">— choose —</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected(old('notification_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-admin.field name="link" label="Link" value="{{ old('link') }}" maxlength="255" />
        </div>
        <x-admin.field name="content" label="Content (best within 80 characters)" value="{{ old('content') }}" required maxlength="80" />
        <button type="submit" class="btn btn-primary">Send notification</button>
    </div>
</form>

@endsection
