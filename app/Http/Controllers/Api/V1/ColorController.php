<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ColorRequest;
use App\Http\Resources\V1\ColorResource;
use App\Models\Color;
use App\Services\ColorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ColorController extends Controller
{
    public function __construct(private readonly ColorService $colors) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ColorResource::collection(
            Color::orderBy('name')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function store(ColorRequest $request): ColorResource
    {
        return new ColorResource($this->colors->create($request->validated())); // 201
    }

    public function show(Color $color): ColorResource
    {
        return new ColorResource($color);
    }

    public function update(ColorRequest $request, Color $color): ColorResource
    {
        return new ColorResource($this->colors->update($color, $request->validated())); // 409 on a code change while in use
    }

    public function destroy(Color $color): Response
    {
        $this->colors->delete($color); // 409 while products offer it

        return response()->noContent();
    }
}
