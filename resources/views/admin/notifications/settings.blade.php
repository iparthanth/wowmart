@extends('admin.layouts.app')
@section('title', 'Notification Settings')
@section('crumbs')
    Notification / Settings
@endsection

@section('content')

<form method="POST" action="{{ route('admin.notifications.settings.save') }}" class="card" style="max-width:760px">
    @csrf
    <div class="card-head"><h2>Notification settings</h2></div>
    <div class="card-body">
        <div class="field">
            <span class="field-label">Which notifications reach the storefront apps</span>
            <div class="row-actions" style="flex-wrap:wrap;gap:12px">
                @foreach (\App\Services\NotificationService::DEFAULT_USER_TYPES as $type)
                    <label class="check">
                        <input type="checkbox" name="types[]" value="{{ $type }}" @checked(in_array($type, $settings['types']))>
                        {{ ucfirst($type) }}
                    </label>
                @endforeach
            </div>
        </div>
        <div class="field" style="margin-top:8px">
            <span class="field-label">Notification design</span>
            <div class="row-actions" style="flex-wrap:wrap;gap:12px">
                @foreach (['text' => 'Only Text', 'design1' => 'Design 1', 'design2' => 'Design 2', 'design3' => 'Design 3'] as $value => $text)
                    <label class="check">
                        <input type="radio" name="notification_show_type" value="{{ $value }}" @checked($settings['show_type'] === $value)>
                        {{ $text }}
                    </label>
                @endforeach
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:12px">Save settings</button>
    </div>
</form>

@endsection
