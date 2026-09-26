<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductTaxRequest;
use App\Http\Requests\Admin\TaxRequest;
use App\Models\Product;
use App\Models\ProductTax;
use App\Models\Tax;
use App\Services\TaxService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function __construct(private readonly TaxService $taxes) {}

    /** The tax list and the product assignments, side by side like the reference panel. */
    public function index(): View
    {
        return view('admin.taxes.index', [
            'taxes' => Tax::withCount('productTaxes')->orderBy('name')->paginate(20),
            'assignments' => ProductTax::with(['product:id,name,slug', 'tax:id,name'])->orderBy('product_id')->paginate(20, ['*'], 'assignments'),
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'allTaxes' => Tax::orderBy('name')->get(['id', 'name']),
            'newTax' => new Tax,
            'newAssignment' => new ProductTax,
        ]);
    }

    public function store(TaxRequest $request): RedirectResponse
    {
        $tax = $this->taxes->create($request->validated());

        return redirect()->route('admin.taxes.index')->with('status', "Tax \"{$tax->name}\" added.");
    }

    public function edit(Tax $tax): View
    {
        return view('admin.taxes.edit', ['tax' => $tax]);
    }

    public function update(TaxRequest $request, Tax $tax): RedirectResponse
    {
        $this->taxes->update($tax, $request->validated());

        return redirect()->route('admin.taxes.edit', $tax)->with('status', 'Changes saved.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        try {
            $this->taxes->delete($tax);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.taxes.index')->with('status', "Tax \"{$tax->name}\" deleted.");
    }

    public function assignStore(ProductTaxRequest $request): RedirectResponse
    {
        try {
            $this->taxes->assign($request->validated());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.taxes.index')->with('status', 'Product tax assigned.');
    }

    public function assignEdit(ProductTax $product_tax): View
    {
        return view('admin.taxes.assign-edit', [
            'assignment' => $product_tax->load(['product:id,name', 'tax:id,name']),
            'taxes' => Tax::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function assignUpdate(ProductTaxRequest $request, ProductTax $product_tax): RedirectResponse
    {
        try {
            $this->taxes->updateAssignment($product_tax, $request->validated());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.taxes.index')->with('status', 'Product tax updated.');
    }

    public function assignDestroy(ProductTax $product_tax): RedirectResponse
    {
        $this->taxes->unassign($product_tax);

        return redirect()->route('admin.taxes.index')->with('status', 'Product tax assignment removed.');
    }
}
