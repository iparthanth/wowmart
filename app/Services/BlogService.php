<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Support\Facades\DB;

/** Every write to blog categories and posts. Input is validated already. */
class BlogService
{
    public function createCategory(array $data): BlogCategory
    {
        return DB::transaction(fn () => BlogCategory::create($data));
    }

    public function updateCategory(BlogCategory $category, array $data): BlogCategory
    {
        return DB::transaction(fn () => tap($category)->update($data));
    }

    /** @throws \DomainException while the category still has posts */
    public function deleteCategory(BlogCategory $category): void
    {
        $posts = $category->posts()->count();

        if ($posts > 0) {
            throw new \DomainException(sprintf(
                '%s still has %s. Move them to another category first.',
                $category->name,
                $posts === 1 ? '1 post' : $posts.' posts',
            ));
        }

        $category->delete();
    }

    public function createPost(array $data): Blog
    {
        return DB::transaction(fn () => Blog::create($data));
    }

    public function updatePost(Blog $post, array $data): Blog
    {
        return DB::transaction(fn () => tap($post)->update($data));
    }

    /** A post can always be deleted. */
    public function deletePost(Blog $post): void
    {
        $post->delete();
    }
}
