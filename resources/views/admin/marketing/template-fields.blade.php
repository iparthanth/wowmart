{{--
    The email-template fields, shared by the inline "Add" form and the edit page. Expects $template (new or stored) and $receivers.
--}}
@php
    $fresh = ! session()->hasOldInput();
    $v = fn (string $key, $stored = null) => $fresh ? $stored : old($key);
@endphp

<div class="split" style="gap:12px">
    <div class="field">
        <label for="f-receiver">Receiver</label>
        <select id="f-receiver" name="receiver" class="input" required>
            @foreach ($receivers as $r)
                <option value="{{ $r }}" @selected(($v('receiver', $template->receiver) ?? 'customer') === $r)>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label class="check" style="margin-top:26px">
            <input type="hidden" name="status" value="0">
            <input id="f-status" type="checkbox" name="status" value="1" @checked($fresh ? (bool) ($template->status ?? true) : old('status') == 1)>
            Active
        </label>
    </div>
</div>

<x-admin.field name="identifier" label="Identifier" :value="$v('identifier', $template->identifier)" required maxlength="60" hint="The event the mail is built from, like order_placed." />
<x-admin.field name="subject" label="Subject" :value="$v('subject', $template->subject)" required maxlength="180" />
<div class="field">
    <label for="f-body">Body</label>
    <textarea id="f-body" name="body" rows="7" class="input" required>{{ $v('body', $template->body) }}</textarea>
</div>
