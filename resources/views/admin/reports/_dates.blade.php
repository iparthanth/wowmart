{{-- Date range for the sales reports. Both days are included; blank means the last 30 days. --}}
<form method="GET" action="{{ $action }}" class="card filters">
    <label class="filter-date">
        <span class="muted">From</span>
        <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="input">
    </label>
    <label class="filter-date">
        <span class="muted">To</span>
        <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="input">
    </label>
    <button type="submit" class="btn btn-ghost">Apply</button>
    @if (request()->hasAny(['date_from', 'date_to']))
        <a href="{{ $action }}" class="btn btn-link">Back to the last 30 days</a>
    @endif
</form>
