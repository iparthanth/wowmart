<div class="field-row" style="margin-bottom:8px">
    <x-admin.field name="fields[ROW][label]" label="Field label" value="" required maxlength="120" />
    <div class="field">
        <label>Field type</label>
        <select name="fields[ROW][type]" class="input">
            @foreach ($types as $value => $text)
                <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
        </select>
    </div>
</div>
