<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Helpers\Helpers\IdenticonHelper;
use CommunityWithLegends\Http\Requests\RegisterRequest;
use CommunityWithLegends\Http\Resources\UserResource;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as Status;

class AdministratorController
{
    public function index(): JsonResponse
    {
        $users = User::role(Role::Administrator)->get();

        return UserResource::collection($users)->response();
    }

    public function delete(User $user): JsonResponse
    {
        if (!$user->hasRole(Role::Administrator) || $user->hasRole(Role::SuperAdministrator)) {
            return response()->json([], Status::HTTP_FORBIDDEN);
        }

        $user->delete();

        return response()->json(
            ["message" => __("admin.deleted", ["name" => $user->name])],
            Status::HTTP_OK,
        );
    }

    public function store(RegisterRequest $request, IdenticonHelper $identiconHelper): JsonResponse
    {
        $validated = $request->validated();

        $user = new User($validated);
        $user->password = Hash::make($validated["password"]);
        $user->save();

        $identiconHelper->create($user->id, $user->email);

        $user->assignRole(Role::Administrator);
        $user->syncPermissions(Role::Administrator->permissions());

        return response()->json(
            ["message" => __("admin.created")],
            Status::HTTP_CREATED,
        );
    }

    public function revokeAdministratorPrivileges(User $user): JsonResponse
    {
        if ($user->hasRole([Role::SuperAdministrator]) || !$user->hasRole(Role::Administrator)) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $user->removeRole(Role::Administrator->value);
        $user->revokePermissionTo(Role::Administrator->permissions());
        $user->givePermissionTo(Role::User->permissions());

        return response()->json(
            ["message" => __("admin.privileges_revoked", ["name" => $user->name])],
            Status::HTTP_OK,
        );
    }
}
