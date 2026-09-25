<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Models\Upload;
use App\Services\BrandService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brands) {}

    /** The list, with the "Add a brand" form beside it. */
    public function index(Request $request): View
    {
        $search = is_string($s = $request->query('search')) ? trim($s) : ''; // ?search[]= is ignored, not a 500

        $brands = Brand::query()
            ->with('logoFile')
            ->withCount('products')
            ->when($search, fn ($q, string $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.brands.index', [
            'brands' => $brands,
            'search' => $search,
            'newBrand' => new Brand,
            'images' => $this->images(),
        ]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $brand = $this->brands->create($request->validated());

        return redirect()->route('admin.brands.index')->with('status', "Brand \"{$brand->name}\" added.");
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', ['brand' => $brand, 'images' => $this->images()]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->brands->update($brand, $request->validated());

        return redirect()->route('admin.brands.edit', $brand)->with('status', 'Changes saved.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        try {
            $this->brands->delete($brand);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.brands.index')->with('status', "Brand \"{$brand->name}\" deleted.");
    }

    public function toggle(Brand $brand, string $field): RedirectResponse
    {
        $this->brands->setFlags($brand, [$field => ! $brand->{$field}]);

        return back()->with('status', sprintf('"%s" is %s a top brand.', $brand->name, $brand->top ? 'now' : 'no longer'));
    }

    private function images()
    {
        // ponytail: whole image library in the picker, same ceiling as the product form.
        return Upload::where('type', 'image')->latest('id')->get();
    }
}
