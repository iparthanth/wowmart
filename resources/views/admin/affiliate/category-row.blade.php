<div class="field-row" style="margin-bottom:8px">
    <div class="field" style="flex:2">
        <label>Category</label>
        <select name="affiliate_category_commissions[{{ $i }}][category_id]" class="input">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>Type</label>
        <select name="affiliate_category_commissions[{{ $i }}][type]" class="input">
            <option value="percent">%</option>
            <option value="amount">$ fixed</option>
        </select>
    </div>
    <x-admin.field name="affiliate_category_commissions[{{ $i }}][value]" label="Value" type="number" step="0.01" min="0" value="" />
</div>
