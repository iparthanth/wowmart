<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Http\Resources\V1\StaffResource;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StaffController extends Controller
{
    public function __construct(private readonly StaffService $staff) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $members = User::whereIn('user_type', [UserType::Admin, UserType::Staff])
            ->orderByRaw('case when user_type = ? then 0 else 1 end', [UserType::Admin->value])
            ->orderBy('name')
            ->with('role:id,name')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString();

        return StaffResource::collection($members);
    }

    public function store(StaffRequest $request): StaffResource
    {
        return new StaffResource($this->staff->create($request->payload())); // 201
    }

    public function show(User $staff): StaffResource
    {
        return new StaffResource($staff->loadMissing('role:id,name'));
    }

    public function update(StaffRequest $request, User $staff): StaffResource
    {
        $this->staff->update($staff, $request->payload()); // 409 on demoting the last admin

        return new StaffResource($staff->loadMissing('role:id,name'));
    }

    public function destroy(Request $request, User $staff): Response
    {
        $this->staff->delete($request->user(), $staff); // 409 on self-delete or the last admin

        return response()->noContent();
    }
}
