<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Helpers\IdenticonHelper;
use CommunityWithLegends\Http\Resources\UserResource;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as Status;

class UserController
{
    public function index(): JsonResponse
    {
        return UserResource::collection(User::withoutRole([Role::Administrator, Role::SuperAdministrator])->get())->response();
    }

    public function show(User $user): JsonResponse
    {
        return UserResource::make($user)->response();
    }

    public function search(Request $request): JsonResponse
    {
        $filter = $request->input("filter");

        if ($filter) {
            $filter = strtolower($filter);
        }

        $users = User::query()
            ->whereRaw("LOWER(name) LIKE ?", ["%" . $filter . "%"])
            ->get();

        return UserResource::collection($users)->response();
    }

    public function ban(User $user, Request $request): JsonResponse
    {
        if ($user->hasRole([Role::Moderator, Role::Administrator, Role::SuperAdministrator])) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $user->revokePermissionTo(Role::User->permissions());

        return response()->json(
            ["message" => "$user->name successfully banned"],
            Status::HTTP_OK,
        );
    }

    public function unban(User $user, Request $request): JsonResponse
    {
        $user->givePermissionTo(Role::User->permissions());

        return response()->json(
            ["message" => "$user->name successfully unbanned"],
            Status::HTTP_OK,
        );
    }

    public function anonymize(User $user, IdenticonHelper $identiconHelper): JsonResponse
    {
        if ($user->hasRole([Role::Moderator, Role::Administrator, Role::SuperAdministrator])) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        foreach ($user->permissions() as $permission) {
            $user->revokePermissionTo($permission);
        }

        $user->email = "$user->id@anonymous.com";
        $user->name = "Anonymous";
        $user->save();

        $identiconHelper->create($user->id, $user->email);

        return response()->json(
            ["message" => "$user->name successfully anonymized"],
            Status::HTTP_OK,
        );
    }

    public function grantModeratorPrivileges(User $user): JsonResponse
    {
        $user->assignRole(Role::Moderator->value);
        $user->givePermissionTo(Role::Moderator->permissions());

        return response()->json(
            ["message" => "Moderator privileges granted to $user->name"],
            Status::HTTP_OK,
        );
    }

    public function revokeModeratorPrivileges(User $user): JsonResponse
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator]) || !$user->hasRole(Role::Moderator)) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $user->removeRole(Role::Moderator->value);
        $user->revokePermissionTo(Role::Moderator->permissions());
        $user->givePermissionTo(Role::User->permissions());

        return response()->json(
            ["message" => "Moderator privileges revoked from $user->name"],
            Status::HTTP_OK,
        );
    }
}
