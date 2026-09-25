<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributeValueRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Services\AttributeService;
use Illuminate\Http\RedirectResponse;

/** The values list on the attribute's edit page. */
class AttributeValueController extends Controller
{
    public function __construct(private readonly AttributeService $attributes) {}

    public function store(AttributeValueRequest $request, Attribute $attribute): RedirectResponse
    {
        $value = $this->attributes->addValue($attribute, $request->validated());

        return redirect()->route('admin.attributes.edit', $attribute)->with('status', "Value \"{$value->value}\" added.");
    }

    public function update(AttributeValueRequest $request, AttributeValue $value): RedirectResponse
    {
        $this->attributes->updateValue($value, $request->validated());

        return redirect()->route('admin.attributes.edit', $value->attribute_id)->with('status', "Value renamed to \"{$value->value}\".");
    }

    public function destroy(AttributeValue $value): RedirectResponse
    {
        $this->attributes->deleteValue($value);

        return redirect()->route('admin.attributes.edit', $value->attribute_id)->with('status', "Value \"{$value->value}\" deleted.");
    }
}
