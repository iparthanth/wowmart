@extends('admin.layouts.app')
@section('title', 'Coupons')
@section('crumbs')
    Coupons
@endsection

@section('content')

<div class="page-actions">
    <p class="muted" style="margin:0">
        @if ($coupons->total() === 0)
            No coupons yet.
        @else
            {{ number_format($coupons->total()) }} {{ Str::plural('coupon', $coupons->total()) }}.
        @endif
    </p>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">Add new coupon</a>
</div>

<div class="card">
    @if ($coupons->isEmpty())
        <div class="empty">
            <strong>No coupons yet</strong>
            Add one to give shoppers a code for a product, cart or first-order discount.
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th class="right">Discount</th>
                        <th>Starts</th>
                        <th>Ends</th>
                        <th>State</th>
                        <th class="right">Times used</th>
                        <th class="wrap">Active</th>
                        <th class="tight"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($coupons as $c)
                    @php [$stateLabel, $stateClass] = \App\Models\Coupon::STATES[$c->state()]; @endphp
                    <tr>
                        <td><a href="{{ route('admin.coupons.edit', $c) }}" class="mono product-name">{{ $c->code }}</a></td>
                        <td>{{ $c->type->label() }}</td>
                        <td class="right num">{{ $c->discountLabel() }}</td>
                        <td class="num">{{ $c->start_date?->format('d M Y, H:i') ?? '—' }}</td>
                        <td class="num">{{ $c->end_date?->format('d M Y, H:i') ?? '—' }}</td>
                        <td><span class="pill {{ $stateClass }}">{{ $stateLabel }}</span></td>
                        <td class="right num">{{ number_format($c->usages_count) }}</td>
                        <td><x-admin.switch :action="route('admin.coupons.toggle', [$c, 'status'])" :on="$c->status" :label="'Active: '.$c->code" /></td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('admin.coupons.edit', $c) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}"
                                      onsubmit="return confirm(@js('Delete the coupon '.$c->code.'? This cannot be undone.'))">
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

@if ($coupons->hasPages())
    <div style="margin-top:16px">{{ $coupons->links() }}</div>
@endif

@endsection
