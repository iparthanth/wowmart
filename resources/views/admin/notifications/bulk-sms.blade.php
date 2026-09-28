@extends('admin.layouts.app')
@section('title', 'Bulk SMS')
@section('crumbs')
    Bulk SMS
@endsection

@section('content')

<div class="split" style="align-items:start">
    <form method="POST" action="{{ route('admin.bulk-sms.send') }}" class="card">
        @csrf
        <div class="card-head"><h2>Send bulk SMS</h2></div>
        <div class="card-body">
            <div class="field">
                <label for="sms-phones">Mobile users (one phone number per line)</label>
                <textarea id="sms-phones" name="user_phones" rows="6" class="input" required
                          placeholder="+8801711000011&#10;+8801811000022">{{ old('user_phones') }}</textarea>
            </div>
            <x-admin.field name="template_id" label="Template ID" value="{{ old('template_id') }}" maxlength="60" />
            <div class="field">
                <label for="sms-content">SMS content</label>
                <textarea id="sms-content" name="content" rows="4" class="input" required maxlength="500">{{ old('content') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send SMS</button>
        </div>
    </form>

    <div class="card">
        <div class="card-head"><h3>Sent blasts</h3></div>
        @if ($messages->isEmpty())
            <div class="empty"><strong>No SMS sent yet</strong> Every blast is logged here with its recipient count.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>When</th><th>Content</th><th>Template</th><th class="right">Numbers</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($messages as $message)
                        <tr>
                            <td class="muted" style="white-space:nowrap">{{ $message->created_at->format('j M Y, g:i a') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($message->content, 60) }}</td>
                            <td class="mono muted">{{ $message->template_id ?? '—' }}</td>
                            <td class="right num">{{ $message->recipients_count }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
