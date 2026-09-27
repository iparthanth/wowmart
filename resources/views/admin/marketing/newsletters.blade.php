@extends('admin.layouts.app')
@section('title', 'Newsletters')
@section('crumbs')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Marketing / Newsletters
@endsection

@section('content')

<div class="split">
    <div>
        <div class="card">
            @if ($newsletters->isEmpty())
                <div class="empty">
                    <strong>No newsletters yet</strong>
                    Compose the first one with the form beside this list.
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Newsletter</th>
                                <th>Segment</th>
                                <th>Sent</th>
                                <th class="tight"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($newsletters as $n)
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <span class="product-name">{{ $n->subject }}</span>
                                        @if ($n->sent_at)
                                            <span class="pill pill-info">sent</span>
                                        @else
                                            <span class="pill pill-mute">draft</span>
                                        @endif
                                    </div>
                                    <div class="muted">{{ \Illuminate\Support\Str::limit($n->body, 60) }}</div>
                                </td>
                                <td><span class="pill">{{ ucfirst($n->segment) }}</span></td>
                                <td>{{ $n->sent_at?->format('j M Y, g:i a') ?? '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        @if (! $n->sent_at)
                                            <form method="POST" action="{{ route('admin.marketing.newsletters.send', $n) }}"
                                                  onsubmit="return confirm(@js('Send "'.$n->subject.'" to the '.$n->segment.' segment? A newsletter can only go out once.'))">
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-sm">Send</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.marketing.newsletters.destroy', $n) }}"
                                              onsubmit="return confirm(@js('Delete the newsletter "'.$n->subject.'"? This cannot be undone.'))">
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

        @if ($newsletters->hasPages())
            <div style="margin-top:16px">{{ $newsletters->links() }}</div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.marketing.newsletters.store') }}" class="card">
        @csrf
        <div class="card-head"><h2>Compose a newsletter</h2></div>
        <div class="card-body">
            @php
                $fresh = ! session()->hasOldInput();
                $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
            @endphp
            <x-admin.field name="subject" label="Subject" :value="$v('subject', $newNewsletter->subject)" required maxlength="180" />
            <div class="field">
                <label for="f-body">Body</label>
                <textarea id="f-body" name="body" rows="6" class="input" required>{{ $v('body', $newNewsletter->body) }}</textarea>
            </div>
            <div class="field">
                <label for="f-segment">Send to</label>
                <select id="f-segment" name="segment" class="input" required>
                    @foreach ($segments as $s)
                        <option value="{{ $s }}" @selected(($v('segment') ?? 'all') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <p class="hint">Subscribers covers the opt-in list, customers covers registered buyers; a newsletter goes out once.</p>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save as draft</button>
        </div>
    </form>
</div>

@endsection
