@extends('admin.layouts.app')
@use('App\Enums\RefundStatus')
@section('title', 'Refund requests')
@section('crumbs')
    Sales
@endsection

@section('content')

@php
    $tones = ['pending' => 'warn', 'approved' => 'ok', 'rejected' => 'bad'];
    $current = $filters['status'];
    $searching = $filters['search'] !== '';
@endphp

<div class="page-actions">
    <nav class="refund-tabs" aria-label="Refund status">
        @foreach (RefundStatus::cases() as $s)
            <a href="{{ route('admin.refunds.index', array_filter(['status' => $s->value, 'search' => $filters['search']])) }}"
               @if ($current === $s) aria-current="page" @endif>
                {{ $s->label() }} <span class="num">{{ number_format($counts[$s->value] ?? 0) }}</span>
            </a>
        @endforeach
    </nav>
    <a href="{{ route('admin.refunds.settings') }}" class="btn btn-ghost">Refund configuration</a>
</div>

<form method="GET" action="{{ route('admin.refunds.index') }}" class="card filters" role="search">
    <input type="hidden" name="status" value="{{ $current->value }}">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="Order code or customer name">
    <button type="submit" class="btn btn-ghost">Search</button>
    @if ($searching)
        <a href="{{ route('admin.refunds.index', ['status' => $current->value]) }}" class="btn btn-link">Clear search</a>
    @endif
</form>

<div class="card">
    @if ($refunds->isEmpty())
        <div class="empty">
            @if ($searching)
                <strong>Nothing matches</strong>
                No {{ strtolower($current->label()) }} refund matches that order code or customer name.
            @else
                <strong>No {{ strtolower($current->label()) }} refunds</strong>
                {{ $current === RefundStatus::Pending ? 'New requests appear here when customers ask for their money back.' : 'Refunds you '.strtolower($current->label()).' appear here.' }}
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Seller</th>
                        <th class="right">Amount</th>
                        <th>Reason</th>
                        <th>Requested</th>
                        <th>Status</th>
                        @if ($current === RefundStatus::Pending)
                            <th class="tight"><span class="sr-only">Actions</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @foreach ($refunds as $refund)
                    @php
                        $line = $refund->orderDetail;
                        $reason = (string) $refund->reason;
                    @endphp
                    <tr>
                        {{-- Never null: deleting an order or its line deletes its refund requests too (cascade). --}}
                        <td><a href="{{ route('admin.orders.show', $refund->order) }}" class="mono product-name">{{ $refund->order->code }}</a></td>
                        <td>
                            @if ($line?->product)
                                {{ $line->product->name }}
                            @else
                                <span class="faint">Deleted product</span>
                            @endif
                            @if ($line)
                                <div class="product-meta">{{ $line->quantity }} × ৳{{ number_format($line->price, 2) }}@if ($line->variation) · {{ $line->variation }}@endif</div>
                            @endif
                        </td>
                        <td>{{ $refund->customer?->name ?? 'Deleted customer' }}</td>
                        <td class="muted">{{ $refund->seller ? ($refund->seller->shop?->name ?? $refund->seller->name) : 'In-house' }}</td>
                        <td class="right num" style="white-space:nowrap">৳{{ number_format($refund->refund_amount, 2) }}</td>
                        <td class="refund-reason">
                            @if ($reason === '')
                                <span class="faint">No reason given</span>
                            @elseif (mb_strlen($reason) <= 120)
                                {{ $reason }}
                            @else
                                <details>
                                    <summary>{{ Str::limit($reason, 120) }}</summary>
                                    {{ $reason }}
                                </details>
                            @endif
                        </td>
                        <td class="muted" style="white-space:nowrap">{{ $refund->created_at->timezone('Asia/Dhaka')->format('j M Y') }}</td>
                        <td><span class="pill pill-{{ $tones[$refund->refund_status->value] }}">{{ $refund->refund_status->label() }}</span></td>
                        @if ($current === RefundStatus::Pending)
                            <td>
                                <div class="row-actions">
                                    <form method="POST" action="{{ route('admin.refunds.approve', $refund) }}"
                                          onsubmit="return confirm(@js('Approve this refund of ৳'.number_format($refund->refund_amount, 2).'? You will need to pay the customer back by hand.'))">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Approve refund</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.refunds.reject', $refund) }}"
                                          onsubmit="return confirm(@js('Reject this refund request? This cannot be undone.'))">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm link-danger">Reject</button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if ($refunds->hasPages())
    <div style="margin-top:16px">{{ $refunds->links() }}</div>
@endif

@endsection
