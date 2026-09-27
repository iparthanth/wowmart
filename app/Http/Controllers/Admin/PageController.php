<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageService $pages) {}

    /** The list, with the "Add a page" form beside it. */
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::orderBy('title')->paginate(20),
            'newPage' => new Page,
        ]);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $page = $this->pages->create($request->validated());

        return redirect()->route('admin.pages.index')->with('status', "Page \"{$page->title}\" added.");
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $this->pages->update($page, $request->validated());

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Changes saved.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $title = $page->title;
        $this->pages->delete($page);

        return redirect()->route('admin.pages.index')->with('status', "Page \"{$title}\" deleted.");
    }
}
