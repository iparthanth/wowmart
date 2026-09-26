@extends('admin.layouts.app')
@php
    $isPayments = $kind === 'payments';
    $title = $isPayments ? 'Payment histories' : 'Collection histories';
    $timestamp = $isPayments ? 'paid_at' : 'collected_at';
    $dateLabel = $isPayments ? 'Paid on' : 'Collected on';
    $storeRoute = $isPayments ? 'admin.delivery-boys.payments.store' : 'admin.delivery-boys.collections.store';
@endphp
@section('title', $title)
@section('crumbs')
    <a href="{{ route('admin.delivery-boys.index') }}">Delivery Boys</a> / {{ $title }}
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            <div class="card-head"><h2>{{ $title }}</h2></div>
            @if ($rows->isEmpty())
                <div class="empty">
                    <strong>Nothing recorded yet</strong>
                    @if ($isPayments)
                        Payouts to delivery boys appear here once recorded.
                    @else
                        Cash collected from delivery boys appears here once recorded.
                    @endif
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Delivery boy</th>
                                <th>Amount</th>
                                <th>{{ $dateLabel }}</th>
                                <th class="wrap">Note</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="product-name">{{ $row->deliveryBoy?->name ?? 'user #'.$row->delivery_boy_id }}</td>
                                <td class="mono">{{ $row->amount }}</td>
                                <td>{{ ($isPayments ? $row->paid_at : $row->collected_at)?->format('M j, Y') }}</td>
                                <td class="wrap">{{ $row->note }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($rows->hasPages())
            <div style="margin-top:16px">{{ $rows->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route($storeRoute) }}" class="card">
        @csrf
        <div class="card-head">
            <h2>{{ $isPayments ? 'Record a payment' : 'Record a collection' }}</h2>
        </div>
        <div class="card-body">
            <div class="field">
                <label for="f-delivery_boy_id">Delivery boy</label>
                <select id="f-delivery_boy_id" name="delivery_boy_id" class="input" required>
                    <option value="">- choose -</option>
                    @foreach ($boys as $b)
                        <option value="{{ $b->id }}" @selected(old('delivery_boy_id') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
                @if ($errors->has('delivery_boy_id'))<span class="error">{{ $errors->first('delivery_boy_id') }}</span>@endif
            </div>

            <x-admin.field name="amount" label="Amount" type="number" step="0.01" min="0.01" :value="old('amount')" required />
            <x-admin.field name="note" label="Note" :value="old('note')" maxlength="200"
                           hint="{{ $isPayments ? 'Like weekly payout, cash bonus.' : 'Like cash from orders #101, #102.' }}" />
            <x-admin.field name="{{$timestamp}}" label="{{ $dateLabel }}" type="date" :value="old($timestamp, now()->toDateString())" />

            <button type="submit" class="btn btn-primary btn-block">{{ $isPayments ? 'Record payment' : 'Record collection' }}</button>
        </div>
    </form>
</div>

@endsection
