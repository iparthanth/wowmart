<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WarrantyRequest;
use App\Models\Warranty;
use App\Services\WarrantyService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WarrantyController extends Controller
{
    public function __construct(private readonly WarrantyService $warranties) {}

    /** The list, with the "Add a warranty" form beside it. */
    public function index(): View
    {
        return view('admin.warranties.index', [
            'warranties' => Warranty::withCount('products')->orderBy('text')->paginate(20),
        ]);
    }

    public function store(WarrantyRequest $request): RedirectResponse
    {
        $warranty = $this->warranties->create($request->validated());

        return redirect()->route('admin.warranties.index')->with('status', "Warranty \"{$warranty->text}\" added.");
    }

    public function edit(Warranty $warranty): View
    {
        return view('admin.warranties.edit', ['warranty' => $warranty]);
    }

    public function update(WarrantyRequest $request, Warranty $warranty): RedirectResponse
    {
        $this->warranties->update($warranty, $request->validated());

        return redirect()->route('admin.warranties.edit', $warranty)->with('status', 'Changes saved.');
    }

    public function destroy(Warranty $warranty): RedirectResponse
    {
        try {
            $this->warranties->delete($warranty);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.warranties.index')->with('status', "Warranty \"{$warranty->text}\" deleted.");
    }
}
