<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    private const FILES = ['iconFile', 'bannerFile', 'coverFile'];

    public function __construct(private readonly CategoryService $categories) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = trim($request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? '');

        // Parents before children, then the shop's own order; paging a tree walk would split branches.
        $query = Category::query()
            ->with(self::FILES)
            ->withCount('products')
            ->when($search !== '', fn (Builder $q) => $q->nameContains($search))
            ->orderBy('level')->orderBy('order_level')->orderBy('name')->orderBy('id');

        return CategoryResource::collection(
            $query->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category->load(self::FILES)->loadCount('products'));
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->categories->create($request->validated());

        return $this->show($category)->response()->setStatusCode(201);
    }

    public function update(CategoryRequest $request, Category $category): CategoryResource
    {
        return $this->show($this->categories->update($category, $request->validated()));
    }

    public function destroy(Category $category): Response
    {
        $this->categories->delete($category);

        return response()->noContent();
    }

    public function flags(Request $request, Category $category): CategoryResource
    {
        $flags = $request->validate(['featured' => ['required', 'boolean']], [
            'featured.required' => 'Send at least one flag to change: featured.',
        ]);

        return $this->show($this->categories->setFlags($category, $flags));
    }
}
