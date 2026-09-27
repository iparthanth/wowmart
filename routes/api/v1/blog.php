<?php

use App\Http\Controllers\Api\V1\BlogController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: each request class is a full replacement.
// Blog categories are separate endpoints on the same controller (category* methods),
// because a POST /blog-categories must not run the post store.

Route::apiResource('blogs', BlogController::class)->except('update')->parameters(['blogs' => 'blog']);
Route::put('blogs/{blog}', [BlogController::class, 'update'])->name('blogs.update');

Route::get('blog-categories', [BlogController::class, 'categoryIndex'])->name('blog-categories.index');
Route::post('blog-categories', [BlogController::class, 'categoryStore'])->name('blog-categories.store');
Route::get('blog-categories/{blog_category}', [BlogController::class, 'categoryShow'])->name('blog-categories.show');
Route::put('blog-categories/{blog_category}', [BlogController::class, 'categoryUpdate'])->name('blog-categories.update');
Route::delete('blog-categories/{blog_category}', [BlogController::class, 'categoryDestroy'])->name('blog-categories.destroy');
