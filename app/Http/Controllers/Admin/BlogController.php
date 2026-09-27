<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\BlogService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(private readonly BlogService $blogs) {}

    /** Posts with their categories, plus the "Add a post" form. */
    public function index(): View
    {
        return view('admin.blog.index', [
            'posts' => Blog::with('category')->orderBy('title')->paginate(20),
            'newPost' => new Blog,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function store(BlogRequest $request): RedirectResponse
    {
        $post = $this->blogs->createPost($request->validated());

        return redirect()->route('admin.blog.index')->with('status', "Post \"{$post->title}\" added.");
    }

    public function edit(Blog $blog): View
    {
        return view('admin.blog.edit', [
            'post' => $blog,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function update(BlogRequest $request, Blog $blog): RedirectResponse
    {
        $this->blogs->updatePost($blog, $request->validated());

        return redirect()->route('admin.blog.edit', $blog)->with('status', 'Changes saved.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $title = $blog->title;
        $this->blogs->deletePost($blog);

        return redirect()->route('admin.blog.index')->with('status', "Post \"{$title}\" deleted.");
    }

    /** Categories with their post counts, plus the "Add a category" form. */
    public function categories(): View
    {
        return view('admin.blog.categories', [
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->paginate(20),
            'newCategory' => new BlogCategory,
        ]);
    }

    public function storeCategory(BlogCategoryRequest $request): RedirectResponse
    {
        $category = $this->blogs->createCategory($request->validated());

        return redirect()->route('admin.blog-categories.index')->with('status', "Category \"{$category->name}\" added.");
    }

    public function editCategory(BlogCategory $blog_category): View
    {
        return view('admin.blog.category-edit', ['category' => $blog_category]);
    }

    public function updateCategory(BlogCategoryRequest $request, BlogCategory $blog_category): RedirectResponse
    {
        $this->blogs->updateCategory($blog_category, $request->validated());

        return redirect()->route('admin.blog-categories.edit', $blog_category)->with('status', 'Changes saved.');
    }

    public function destroyCategory(BlogCategory $blog_category): RedirectResponse
    {
        try {
            $this->blogs->deleteCategory($blog_category);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.blog-categories.index')->with('status', "Category \"{$blog_category->name}\" deleted.");
    }
}
