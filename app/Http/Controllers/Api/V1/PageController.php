<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Http\Resources\V1\PageResource;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function __construct(private readonly PageService $pages) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return PageResource::collection(
            Page::orderBy('title')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function store(PageRequest $request): PageResource
    {
        return new PageResource($this->pages->create($request->validated())); // 201
    }

    public function show(Page $page): PageResource
    {
        return new PageResource($page);
    }

    public function update(PageRequest $request, Page $page): PageResource
    {
        return new PageResource($this->pages->update($page, $request->validated()));
    }

    public function destroy(Page $page): Response
    {
        $this->pages->delete($page);

        return response()->noContent();
    }
}
