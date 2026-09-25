<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    private const RELATIONS = ['owner:id,name', 'thumbnail', 'gallery', 'categories:id', 'flashDeals:id', 'stocks'];

    private const FLAGS = ['published', 'approved', 'featured', 'todays_deal'];

    public function __construct(private readonly ProductService $products) {}

    /** Same filters as the panel list (search, added_by, category, stock, image, published, sort). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::with(self::RELATIONS)
            ->adminFilter(Product::adminFilters($request->query()))
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    /** Columns that are NOT NULL with a zero default: over JSON, null means "none", which is stored as 0. */
    private const ZERO_WHEN_EMPTY = ['weight', 'discount', 'shipping_cost'];

    public function store(ProductRequest $request): ProductResource
    {
        return $this->show($this->products->create($this->zeroed($request->validated()), $request->user())->refresh()); // 201: model was just created; refresh picks up column defaults
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(self::RELATIONS));
    }

    /**
     * PUT replaces the whole product with the panel form's rules, so EVERY omitted
     * field means empty / none, as an empty input on the form does: optional
     * columns are cleared (0 for weight, discount, shipping cost), unsent switches
     * are off, and an omitted photos / category_ids / flash_deal_id clears the
     * gallery, extra categories and flash deal. Keeping omitted values instead
     * would let a stored discount skip the discount rules (a flat 150 kept as a
     * 150% discount after the type changed). A GET body can be sent back as is.
     */
    public function update(ProductRequest $request, Product $product): ProductResource
    {
        $fields = array_filter(array_keys($request->rules()), fn (string $key) => ! str_contains($key, '.'));
        $data = array_replace(array_fill_keys($fields, null), $request->validated());

        return $this->show($this->products->update($product, $this->zeroed($data)));
    }

    public function destroy(Product $product): Response
    {
        $this->products->delete($product); // 409 once it is on an order

        return response()->noContent();
    }

    public function duplicate(Product $product): JsonResponse
    {
        return $this->show($this->products->duplicate($product)->refresh())->response()->setStatusCode(201);
    }

    /** Sets exactly the given switches (idempotent, unlike the panel's one-click toggle). */
    public function flags(Request $request, Product $product): ProductResource
    {
        $flags = $request->validate(array_fill_keys(self::FLAGS, ['sometimes', 'boolean']));

        if ($flags === []) {
            throw ValidationException::withMessages(['flags' => 'Send at least one of: '.implode(', ', self::FLAGS).'.']);
        }

        $product->forceFill(array_map(fn ($v) => (bool) $v, $flags))->save();

        return $this->show($product);
    }

    private function zeroed(array $data): array
    {
        foreach (self::ZERO_WHEN_EMPTY as $key) {
            $data[$key] ??= 0;
        }

        return $data;
    }
}
