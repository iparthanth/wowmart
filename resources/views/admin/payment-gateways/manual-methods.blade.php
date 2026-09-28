@extends('admin.layouts.app')
@section('title', 'Manual Payment Methods')
@section('crumbs')
    Payment Gateways / Manual Payment Methods
@endsection

@section('content')

<div class="split" style="align-items:start">
    <div class="card">
        <div class="card-head"><h2>Manual payment methods</h2></div>
        @if ($methods->isEmpty())
            <div class="empty">
                <strong>No manual payment methods yet</strong>
                Bank transfers, bKash, Nagad — anything the buyer pays by hand — are defined here and offered at checkout.
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Heading</th>
                            <th>Logo</th>
                            <th>Status</th>
                            <th class="tight"><span class="sr-only">Options</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($methods as $method)
                        <tr>
                            <td class="muted num">{{ $method->id }}</td>
                            <td>{{ $method->name }}</td>
                            <td>
                                @if ($method->logoUpload)
                                    <img src="{{ $method->logoUpload->url() }}" alt="" style="max-height:28px;max-width:72px">
                                @else
                                    <span class="faint">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($method->status)
                                    <span class="pill pill-ok">active</span>
                                @else
                                    <span class="pill pill-mute">off</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.manual-methods.edit', $method) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.manual-methods.destroy', $method) }}" style="display:inline"
                                      onsubmit="return confirm(@js('Delete '.$method->name.'? Buyers will no longer see it at checkout.'))">
                                    @csrf
                                    @method('DELETE')
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

    <form method="POST" action="{{ route('admin.manual-methods.store') }}" class="card" style="max-width:420px">
        @csrf
        <div class="card-head"><h3>Add manual payment method</h3></div>
        <div class="card-body">
            <x-admin.field name="name" label="Heading" value="{{ old('name') }}" required maxlength="80" />
            <div class="field">
                <span class="field-label">Logo</span>
                <x-admin.media-picker name="logo" :images="$images" :selected="old('logo')" />
            </div>
            <div class="field">
                <label for="m-instructions">Payment instructions</label>
                <textarea id="m-instructions" name="instructions" rows="4" class="input" maxlength="5000">{{ old('instructions') }}</textarea>
            </div>
            <label class="check">
                <input type="hidden" name="status" value="0">
                <input type="checkbox" name="status" value="1" @checked(old('status', '1') === '1')>
                Active
            </label>
            <button type="submit" class="btn btn-primary" style="margin-top:12px">Add method</button>
        </div>
    </form>
</div>

@endsection
