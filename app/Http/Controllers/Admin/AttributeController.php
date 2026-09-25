<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributeRequest;
use App\Models\Attribute;
use App\Services\AttributeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttributeController extends Controller
{
    public function __construct(private readonly AttributeService $attributes) {}

    /** The list, with the "Add an attribute" form beside it. */
    public function index(Request $request): View
    {
        $search = is_string($s = $request->query('search')) ? trim($s) : ''; // ?search[]= is ignored, not a 500

        $attributes = Attribute::query()
            ->with(['values' => fn ($q) => $q->orderBy('value')])
            ->withCount('values')
            ->when($search, fn ($q, string $s) => $q->whereContains('name', $s))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attributes.index', ['attributes' => $attributes, 'search' => $search]);
    }

    public function store(AttributeRequest $request): RedirectResponse
    {
        $attribute = $this->attributes->create($request->validated());

        // Straight to its values: an attribute with none offers nothing on the product form.
        return redirect()->route('admin.attributes.edit', $attribute)
            ->with('status', "Attribute \"{$attribute->name}\" added. Now add its values.");
    }

    public function edit(Attribute $attribute): View
    {
        return view('admin.attributes.edit', [
            'attribute' => $attribute,
            'values' => $attribute->values()->orderBy('value')->get(),
        ]);
    }

    public function update(AttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        $this->attributes->update($attribute, $request->validated());

        return redirect()->route('admin.attributes.edit', $attribute)->with('status', 'Name saved.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        try {
            $this->attributes->delete($attribute);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.attributes.index')->with('status', "Attribute \"{$attribute->name}\" deleted.");
    }
}
