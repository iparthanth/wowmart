<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ColorRequest;
use App\Models\Color;
use App\Services\ColorService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ColorController extends Controller
{
    public function __construct(private readonly ColorService $colors) {}

    /** The list, with the "Add a colour" form beside it. */
    public function index(): View
    {
        return view('admin.colors.index', [
            'colors' => Color::orderBy('name')->paginate(20),
            'newColor' => new Color,
        ]);
    }

    public function store(ColorRequest $request): RedirectResponse
    {
        $color = $this->colors->create($request->validated());

        return redirect()->route('admin.colors.index')->with('status', "Colour \"{$color->name}\" added.");
    }

    public function edit(Color $color): View
    {
        return view('admin.colors.edit', ['color' => $color]);
    }

    public function update(ColorRequest $request, Color $color): RedirectResponse
    {
        try {
            $this->colors->update($color, $request->validated());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.colors.edit', $color)->with('status', 'Changes saved.');
    }

    public function destroy(Color $color): RedirectResponse
    {
        try {
            $this->colors->delete($color);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.colors.index')->with('status', "Colour \"{$color->name}\" deleted.");
    }
}
