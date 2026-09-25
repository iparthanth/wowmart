<?php

use App\Http\Controllers\Api\V1\UploadController;
use Illuminate\Support\Facades\Route;

Route::apiResource('uploads', UploadController::class)->except('update');
