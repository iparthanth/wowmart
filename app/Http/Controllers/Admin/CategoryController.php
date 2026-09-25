<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Upload;
use App\Services\CategoryService;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function index(Request $request): View
    {
        // A hand-edited ?search[]= is ignored rather than cast (which would 500).
        $search = is_string($s = $request->query('search')) ? trim($s) : '';

        // ponytail: the whole tree on one page, unpaged. Fine for hundreds of
        // categories; page by top-level branch if it ever grows past that.
        $list = $search === ''
            ? Collection::make(Category::inTreeOrder()->all())
            : Category::nameContains($search)->orderBy('name')->get();

        return view('admin.categories.index', [
            'categories' => $list->load(['parent:id,name', 'iconFile']),
            'search' => $search,
            // One grouped query for every row. The main category is always also
            // in product_categories (ProductService adds it), so this counts it too.
            'productCounts' => DB::table('product_categories')
                ->selectRaw('category_id, count(*) as n')
                ->groupBy('category_id')
                ->pluck('n', 'category_id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', $this->formData(new Category(['order_level' => 0])));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = $this->categories->create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('status', "Category \"{$category->name}\" created.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', $this->formData($category));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated());

        return redirect()->route('admin.categories.edit', $category)->with('status', 'Changes saved.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $this->categories->delete($category);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.categories.index')->with('status', "Category \"{$category->name}\" deleted.");
    }

    public function toggle(Category $category, string $field): RedirectResponse
    {
        $this->categories->setFlags($category, [$field => ! $category->{$field}]);

        return back()->with('status', sprintf('"%s" is %s featured.', $category->name, $category->{$field} ? 'now' : 'no longer'));
    }

    private function formData(Category $category): array
    {
        // A category cannot be moved under itself or its own branch, so those are not offered.
        $blocked = $category->exists ? [$category->id, ...$category->descendantIds()] : [];

        return [
            'category' => $category,
            'parents' => Category::inTreeOrder()->reject(fn ($c) => in_array($c->id, $blocked, true)),
            'images' => Upload::where('type', 'image')->latest('id')->get(),
        ];
    }
}
