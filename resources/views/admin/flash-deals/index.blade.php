@use('App\Models\FlashDeal')
@extends('admin.layouts.app')
@section('title', 'Flash deals')
@section('crumbs')
    Flash deals
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        @if ($deals->total() === 0)
            No flash deals yet.
        @else
            {{ number_format($deals->total()) }} {{ Str::plural('flash deal', $deals->total()) }}. Only one can be featured at a time.
        @endif
    </p>
    <a href="{{ route('admin.flash-deals.create') }}" class="btn btn-primary">Add new flash deal</a>
</div>

<div class="card">
    @if ($deals->isEmpty())
        <div class="empty">
            <strong>No flash deals yet</strong>
            Add one to put a set of products on a timed discount with its own landing page.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Starts</th>
                        <th>Ends</th>
                        <th class="right">Products</th>
                        <th>State</th>
                        <th class="wrap">Active</th>
                        <th class="wrap">Featured</th>
                        <th>Page</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($deals as $d)
                    @php [$stateLabel, $stateClass] = FlashDeal::STATES[$d->state()]; @endphp
                    <tr>
                        <td>
                            <div class="product-cell">
                                @if ($d->bannerFile)
                                    <img src="{{ $d->bannerFile->url() }}" alt="" class="thumb" loading="lazy">
                                @else
                                    <span class="thumb thumb-missing" title="No banner"></span>
                                @endif
                                <a href="{{ route('admin.flash-deals.edit', $d) }}" class="product-name">{{ $d->title }}</a>
                            </div>
                        </td>
                        <td class="num">{{ $d->start_date->format('d M Y, H:i') }}</td>
                        <td class="num">{{ $d->end_date->format('d M Y, H:i') }}</td>
                        <td class="right num">{{ number_format($d->products_count) }}</td>
                        <td><span class="pill {{ $stateClass }}">{{ $stateLabel }}</span></td>
                        <td><x-admin.switch :action="route('admin.flash-deals.toggle', [$d, 'status'])" :on="$d->status" :label="'Active: '.$d->title" /></td>
                        <td><x-admin.switch :action="route('admin.flash-deals.toggle', [$d, 'featured'])" :on="$d->featured" :label="'Featured: '.$d->title" /></td>
                        <td><a href="{{ $d->pageUrl() }}" target="_blank" rel="noopener">Open page<span class="sr-only"> for {{ $d->title }} (new tab)</span></a></td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('admin.flash-deals.edit', $d) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.flash-deals.destroy', $d) }}"
                                      onsubmit="return confirm(@js('Delete the flash deal "'.$d->title.'"? Its products go back to their normal prices. This cannot be undone.'))">
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

@if ($deals->hasPages())
    <div style="margin-top:16px">{{ $deals->links() }}</div>
@endif

@endsection
