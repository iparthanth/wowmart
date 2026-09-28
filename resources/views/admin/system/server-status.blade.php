@extends('admin.layouts.app')
@section('title', 'Server Status')
@section('crumbs')
    System / Server Status
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head"><h2>Core</h2></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Name</th><th>Current version</th><th>Required version</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($requirements as $requirement)
                        <tr>
                            <td>{{ $requirement['name'] }}</td>
                            <td class="mono">{{ $requirement['current'] }}</td>
                            <td class="mono muted">{{ $requirement['required'] }}</td>
                            <td>
                                @if ($requirement['ok'])
                                    <span class="pill pill-ok">ok</span>
                                @else
                                    <span class="pill pill-bad">upgrade</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-head"><h2>PHP configuration</h2></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Config name</th><th>Current</th><th>Recommended</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($configs as $config)
                        <tr>
                            <td class="mono">{{ $config['name'] }}</td>
                            <td class="mono">{{ $config['current'] ?: '?' }}</td>
                            <td class="mono muted">{{ $config['recommended'] }}</td>
                            <td>
                                @if ($config['ok'])
                                    <span class="pill pill-ok">ok</span>
                                @else
                                    <span class="pill pill-warn">check</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-head"><h2>Extensions</h2></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Extension name</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($extensions as $extension)
                        <tr>
                            <td class="mono">{{ $extension['name'] }}</td>
                            <td>
                                @if ($extension['ok'])
                                    <span class="pill pill-ok">installed</span>
                                @else
                                    <span class="pill pill-bad">missing</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <div class="card-head"><h2>File permissions</h2></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>File or folder</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($writable as $path)
                        <tr>
                            <td class="mono">{{ $path['path'] }}</td>
                            <td>
                                @if ($path['ok'])
                                    <span class="pill pill-ok">writable</span>
                                @else
                                    <span class="pill pill-bad">not writable</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
