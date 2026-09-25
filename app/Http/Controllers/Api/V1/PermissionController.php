<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;

/** The permission registry, so clients can render the same checkboxes the panel does. */
class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => collect($this->permissions->all())
                ->map(fn (array $definition, string $key) => [
                    'key' => $key,
                    'label' => $definition['label'],
                    'patterns' => $definition['patterns'],
                ])
                ->values()
                ->all(),
        ]);
    }
}
