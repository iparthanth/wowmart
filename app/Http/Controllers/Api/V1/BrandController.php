<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Http\Resources\V1\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brands) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = is_string($s = $request->query('search')) ? trim($s) : ''; // ?search[]= is ignored, not a 500

        $brands = Brand::query()
            ->with('logoFile')
            ->withCount('products')
            ->when($search, fn ($q, string $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return BrandResource::collection($brands);
    }

    public function store(BrandRequest $request): BrandResource
    {
        return $this->show($this->brands->create($request->validated())); // 201: model was just created
    }

    public function show(Brand $brand): BrandResource
    {
        return new BrandResource($brand->load('logoFile')->loadCount('products'));
    }

    /** PUT replaces the whole brand, with the same rules as the panel's form. */
    public function update(BrandRequest $request, Brand $brand): BrandResource
    {
        return $this->show($this->brands->update($brand, $request->validated()));
    }

    public function flags(Request $request, Brand $brand): BrandResource
    {
        $flags = $request->validate(['top' => ['required', 'boolean']]);

        return $this->show($this->brands->setFlags($brand, ['top' => (bool) $flags['top']]));
    }

    public function destroy(Brand $brand): Response
    {
        $this->brands->delete($brand); // 409 while products use it

        return response()->noContent();
    }
}
