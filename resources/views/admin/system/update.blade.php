@extends('admin.layouts.app')
@section('title', 'System Update')
@section('crumbs')
    System / Update
@endsection

@section('content')

<div class="split">
    <form method="POST" action="{{ route('admin.system.apply-update') }}" enctype="multipart/form-data" class="card">
        @csrf
        <div class="card-head"><h2>Update system</h2></div>
        <div class="card-body">
            <p class="muted">WowMart runs version <strong>{{ $version }}</strong>. An update package is a zip built by the release process; it is filed here and applied by hand, following the release notes.</p>
            <div class="field">
                <label for="u-zip">Update package (.zip)</label>
                <input id="u-zip" type="file" name="update_zip" accept=".zip" class="input" required>
            </div>
            <button type="submit" class="btn btn-primary">File update package</button>
        </div>
    </form>

    <div class="card">
        <div class="card-head"><h3>Filed packages</h3></div>
        <div class="card-body">
            @if ($lastCheck)
                <p class="muted">Last activity {{ \Illuminate\Support\Carbon::parse($lastCheck)->format('j M Y, g:i a') }}</p>
            @endif
            @if ($packages->isEmpty())
                <div class="empty"><strong>No packages filed</strong> Update zips appear here with the date they were received.</div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>File</th><th>Size</th><th>Filed</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($packages as $package)
                            <tr>
                                <td class="mono">{{ $package['original'] }}</td>
                                <td class="muted num">{{ number_format($package['size'] / 1024, 1) }} KB</td>
                                <td class="muted">{{ \Illuminate\Support\Carbon::parse($package['filed_at'])->format('j M Y, g:i a') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
