<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributeValueRequest;
use App\Http\Resources\V1\AttributeValueResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Services\AttributeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AttributeValueController extends Controller
{
    public function __construct(private readonly AttributeService $attributes) {}

    public function index(Request $request, Attribute $attribute): AnonymousResourceCollection
    {
        return AttributeValueResource::collection(
            $attribute->values()->orderBy('value')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function store(AttributeValueRequest $request, Attribute $attribute): AttributeValueResource
    {
        return new AttributeValueResource($this->attributes->addValue($attribute, $request->validated())); // 201
    }

    public function update(AttributeValueRequest $request, AttributeValue $value): AttributeValueResource
    {
        return new AttributeValueResource($this->attributes->updateValue($value, $request->validated()));
    }

    public function destroy(AttributeValue $value): Response
    {
        $this->attributes->deleteValue($value);

        return response()->noContent();
    }
}
