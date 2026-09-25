@extends('admin.layouts.app')
@section('title', 'Refund configuration')
@section('crumbs')
    <a href="{{ route('admin.refunds.index') }}">Refund requests</a> / Configuration
@endsection

@section('content')

<form method="POST" action="{{ route('admin.refunds.settings.update') }}" class="card" style="max-width:560px">
    @csrf
    @method('PUT')
    <div class="card-head">
        <h2>Refund window</h2>
    </div>
    <div class="card-body">
        <x-admin.field name="refund_request_time" label="Days after delivery" type="number"
                       :value="session()->hasOldInput() ? old('refund_request_time') : $days"
                       min="1" max="365" step="1" required
                       hint="How long a customer may still ask for a refund once the order is delivered. 1 to 365 days." />

        <div class="row-actions" style="justify-content:flex-start">
            <button type="submit" class="btn btn-primary">Save refund window</button>
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-link">Back to refund requests</a>
        </div>
    </div>
</form>

@endsection
