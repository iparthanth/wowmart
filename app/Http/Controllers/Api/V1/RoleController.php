<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Http\Resources\V1\RoleResource;
use App\Models\Role;
use App\Services\RoleService;
use DomainException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection(Role::orderBy('name')->with('permissions')->get());
    }

    public function store(RoleRequest $request): RoleResource
    {
        return new RoleResource($this->roles->create($request->validated())); // 201
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource($role->loadMissing('permissions'));
    }

    public function update(RoleRequest $request, Role $role): RoleResource
    {
        return new RoleResource($this->roles->update($role, $request->validated()));
    }

    public function destroy(Role $role): Response
    {
        $this->roles->delete($role); // 409 while staff members hold it

        return response()->noContent();
    }
}
