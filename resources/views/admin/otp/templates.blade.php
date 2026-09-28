@extends('admin.layouts.app')
@section('title', 'SMS Templates')
@section('crumbs')
    OTP System / SMS Templates
@endsection

@section('content')

<div class="card">
    <div class="card-head">
        <h2>SMS templates</h2>
        <span class="muted">Placeholders like <span class="mono">{code}</span> are replaced when the message is sent.</span>
    </div>
    @if ($templates->isEmpty())
        <div class="empty"><strong>No templates yet</strong> Messages sent by the system are defined here.</div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>SMS body</th>
                        <th>Template ID</th>
                        <th>Activation</th>
                        <th class="tight"><span class="sr-only">Save</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($templates as $template)
                    <tr>
                        <td>
                            <strong>{{ $template->name }}</strong>
                            <div class="product-meta muted mono">{{ $template->key }}</div>
                        </td>
                        <td style="min-width:320px">
                            <form id="tpl-{{ $template->id }}" method="POST" action="{{ route('admin.otp.templates.update', $template) }}">
                                @csrf
                                @method('PUT')
                                <textarea name="body" rows="3" class="input" required maxlength="1000">{{ old('body', $template->body) }}</textarea>
                            </form>
                        </td>
                        <td>
                            <input form="tpl-{{ $template->id }}" type="text" name="template_id" class="input" maxlength="60"
                                   value="{{ old('template_id', $template->template_id) }}" placeholder="Provider template ID">
                        </td>
                        <td>
                            <input form="tpl-{{ $template->id }}" type="hidden" name="status" value="0">
                            <input form="tpl-{{ $template->id }}" id="tpl-status-{{ $template->id }}" type="checkbox" name="status" value="1" @checked($template->status)>
                            <label for="tpl-status-{{ $template->id }}" class="sr-only">Active</label>
                        </td>
                        <td>
                            <button form="tpl-{{ $template->id }}" type="submit" class="btn btn-ghost btn-sm">Save</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
