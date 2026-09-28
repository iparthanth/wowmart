@extends('admin.layouts.app')
@section('title', 'Sitemap Generator')
@section('crumbs')
    System / Sitemap Generator
@endsection

@section('content')

<div class="card">
    <div class="card-head">
        <h2>Sitemap generator</h2>
        <form method="POST" action="{{ route('admin.system.sitemaps.generate') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">Generate sitemaps</button>
        </form>
    </div>
    <div class="card-body" style="border-bottom:1px solid var(--line-2)">
        <p class="muted" style="margin:0">Builds fresh XML sitemaps for categories, published products and blog posts into <span class="mono">public/sitemaps</span>. Point robots.txt and webmaster tools at them.</p>
    </div>
    @if (empty($sitemaps))
        <div class="empty">
            <strong>No sitemaps yet</strong>
            Generate them once after big catalogue changes; the files are served straight from the storefront.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>File name</th>
                        <th class="right">File size</th>
                        <th>Last modified</th>
                        <th>Mime type</th>
                        <th>Sitemap URL</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($sitemaps as $i => $sitemap)
                    <tr>
                        <td class="muted num">{{ $i + 1 }}</td>
                        <td class="mono">{{ $sitemap['name'] }}</td>
                        <td class="right num">{{ number_format($sitemap['size'] / 1024, 1) }} KB</td>
                        <td class="muted">{{ $sitemap['modified']->format('j M Y, g:i a') }}</td>
                        <td class="muted mono">{{ $sitemap['mime'] }}</td>
                        <td><a href="{{ $sitemap['url'] }}" target="_blank" rel="noopener" class="mono">{{ $sitemap['name'] }}</a></td>
                        <td>
                            <form method="POST" action="{{ route('admin.system.sitemaps.delete', $sitemap['name']) }}"
                                  onsubmit="return confirm(@js('Delete '.$sitemap['name'].'?'))">
                                @csrf
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

@endsection
