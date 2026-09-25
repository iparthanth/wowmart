<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DiscountType;
use App\Enums\ShippingType;
use App\Enums\StockVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\Note;
use App\Models\Product;
use App\Models\Upload;
use App\Models\User;
use App\Models\Warranty;
use App\Services\ProductService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $f = Product::adminFilters($request->query());

        $products = Product::query()
            ->with(['thumbnail', 'owner.shop', 'category'])
            ->adminFilter($f)
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'filters' => $f,
            // Not a bare array_filter: published=0 is a real filter, and '0' is falsy.
            'filtered' => array_filter(array_diff_key($f, ['sort' => 1]), fn ($v) => $v !== null && $v !== '') !== [],
            'categories' => Category::inTreeOrder(),
        ]);
    }

    public function create(): View
    {
        // Pre-filled with the same defaults the table has, so the form opens valid.
        $product = (new Product)->forceFill([
            'unit' => 'pc',
            'min_qty' => 1,
            'low_stock_quantity' => 1,
            'current_stock' => 0,
            'discount_type' => DiscountType::Amount,
            'stock_visibility_state' => StockVisibility::Quantity,
            'shipping_type' => ShippingType::Free,
            'cash_on_delivery' => true,
        ]);

        return view('admin.products.create', $this->formData($product));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();
        $product = $this->products->create($request->validated(), $admin);

        return redirect()->route('admin.products.edit', $product)
            ->with('status', "Product \"{$product->name}\" created.");
    }

    public function edit(Product $product): View
    {
        $product->load(['categories:id', 'gallery:id', 'flashDeals:id', 'owner.shop']);

        return view('admin.products.edit', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated());

        return redirect()->route('admin.products.edit', $product)->with('status', 'Changes saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->products->delete($product);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.products.index')->with('status', "Product \"{$product->name}\" deleted.");
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $copy = $this->products->duplicate($product);

        return redirect()->route('admin.products.edit', $copy)
            ->with('status', 'Copy created. It stays unpublished until you publish it.');
    }

    /** One-click switches on the list: published, approved, featured, today's deal. */
    public function toggle(Product $product, string $field): RedirectResponse
    {
        $product->forceFill([$field => ! $product->{$field}])->save();

        $label = ['published' => 'published', 'approved' => 'approved', 'featured' => 'featured', 'todays_deal' => "a today's deal"][$field];

        return back()->with('status', sprintf('"%s" is %s %s.', $product->name, $product->{$field} ? 'now' : 'no longer', $label));
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::inTreeOrder(),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            // ponytail: the whole image library renders into the picker. Fine for
            // hundreds of files; past that, page it or search it server-side.
            'images' => Upload::where('type', 'image')->latest('id')->get(),
            'documents' => Upload::where('type', 'document')->latest('id')->get(['id', 'file_original_name']),
            'flashDeals' => FlashDeal::orderByDesc('end_date')->get(['id', 'title', 'end_date']),
            'warranties' => Warranty::orderBy('id')->get(),
            'notes' => Note::orderBy('id')->get()->groupBy('type'),
        ];
    }
}
