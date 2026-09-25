<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Admin\UploadController as LibraryController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadRequest;
use App\Http\Resources\V1\UploadResource;
use App\Models\Upload;
use App\Models\User;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UploadController extends Controller
{
    public function __construct(private readonly UploadService $uploads) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $inUse = Upload::idsInUse();

        $files = LibraryController::query(LibraryController::filters($request))
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString()
            ->through(fn (Upload $u) => new UploadResource($u, isset($inUse[$u->id])));

        return UploadResource::collection($files);
    }

    public function show(Upload $upload): UploadResource
    {
        $usages = $upload->usages();

        return new UploadResource($upload, $usages !== [], $usages);
    }

    public function store(UploadRequest $request): JsonResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        // Same per-file saving as the web library; validation has already passed for every file.
        $created = array_map(
            fn ($file) => new UploadResource($this->uploads->store($file, $admin)),
            $request->file('files'),
        );

        return UploadResource::collection($created)->response()->setStatusCode(201);
    }

    public function destroy(Upload $upload): Response
    {
        $this->uploads->delete($upload); // 409 while something still uses it

        return response()->noContent();
    }
}
