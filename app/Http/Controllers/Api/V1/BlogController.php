<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Http\Requests\Admin\BlogRequest;
use App\Http\Resources\V1\BlogCategoryResource;
use App\Http\Resources\V1\BlogResource;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    public function __construct(private readonly BlogService $blogs) {}

    // ------------------------------------------------------------------ posts

    public function index(Request $request): AnonymousResourceCollection
    {
        return BlogResource::collection(
            Blog::with('category', 'bannerFile')->orderBy('title')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function store(BlogRequest $request): BlogResource
    {
        return new BlogResource($this->blogs->createPost($request->validated())->load('category', 'bannerFile')); // 201
    }

    public function show(Blog $blog): BlogResource
    {
        return new BlogResource($blog->load('category', 'bannerFile'));
    }

    public function update(BlogRequest $request, Blog $blog): BlogResource
    {
        return new BlogResource($this->blogs->updatePost($blog, $request->validated()));
    }

    public function destroy(Blog $blog): Response
    {
        $this->blogs->deletePost($blog);

        return response()->noContent();
    }

    // ------------------------------------------------------------- categories

    public function categoryIndex(Request $request): AnonymousResourceCollection
    {
        return BlogCategoryResource::collection(
            BlogCategory::withCount('posts')->orderBy('name')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function categoryStore(BlogCategoryRequest $request): BlogCategoryResource
    {
        return new BlogCategoryResource($this->blogs->createCategory($request->validated())); // 201
    }

    public function categoryShow(BlogCategory $blog_category): BlogCategoryResource
    {
        return new BlogCategoryResource($blog_category);
    }

    public function categoryUpdate(BlogCategoryRequest $request, BlogCategory $blog_category): BlogCategoryResource
    {
        return new BlogCategoryResource($this->blogs->updateCategory($blog_category, $request->validated()));
    }

    public function categoryDestroy(BlogCategory $blog_category): Response
    {
        $this->blogs->deleteCategory($blog_category); // 409 while posts hang under it

        return response()->noContent();
    }
}
