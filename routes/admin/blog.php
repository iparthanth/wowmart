<?php

use App\Http\Controllers\Admin\BlogController;
use Illuminate\Support\Facades\Route;

// The blog, mirroring the reference panel's /admin/blog and /admin/blog-category.
// The route group in web.php already applies the "admin." name prefix.
Route::controller(BlogController::class)->group(function () {
    Route::get('blog', 'index')->name('blog.index');
    Route::post('blog', 'store')->name('blog.store');
    Route::get('blog/{blog}/edit', 'edit')->name('blog.edit');
    Route::put('blog/{blog}', 'update')->name('blog.update');
    Route::delete('blog/{blog}', 'destroy')->name('blog.destroy');

    Route::get('blog-category', 'categories')->name('blog-categories.index');
    Route::post('blog-category', 'storeCategory')->name('blog-categories.store');
    Route::get('blog-category/{blog_category}/edit', 'editCategory')->name('blog-categories.edit');
    Route::put('blog-category/{blog_category}', 'updateCategory')->name('blog-categories.update');
    Route::delete('blog-category/{blog_category}', 'destroyCategory')->name('blog-categories.destroy');
});
