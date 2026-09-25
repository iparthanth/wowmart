<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributeRequest;
use App\Http\Resources\V1\AttributeResource;
use App\Models\Attribute;
use App\Services\AttributeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AttributeController extends Controller
{
    public function __construct(private readonly AttributeService $attributes) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = is_string($s = $request->query('search')) ? trim($s) : ''; // ?search[]= is ignored, not a 500

        $attributes = Attribute::query()
            ->withCount('values')
            ->when($search, fn ($q, string $s) => $q->whereContains('name', $s))
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return AttributeResource::collection($attributes);
    }

    public function store(AttributeRequest $request): AttributeResource
    {
        return $this->show($this->attributes->create($request->validated())); // 201: model was just created
    }

    /** With its values: an attribute is small, and the values are what a client wants. */
    public function show(Attribute $attribute): AttributeResource
    {
        return new AttributeResource($attribute->load(['values' => fn ($q) => $q->orderBy('value')])->loadCount('values'));
    }

    public function update(AttributeRequest $request, Attribute $attribute): AttributeResource
    {
        return $this->show($this->attributes->update($attribute, $request->validated()));
    }

    public function destroy(Attribute $attribute): Response
    {
        $this->attributes->delete($attribute); // 409 while products offer it

        return response()->noContent();
    }
}
