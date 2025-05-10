<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Enums\Permission;
use CommunityWithLegends\Http\Resources\UserResource;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as Status;

class UserController
{
    public function index(): JsonResponse
    {
        return UserResource::collection(User::all())->response();
    }

    public function show(User $user): JsonResponse
    {
        return UserResource::make($user)->response();
    }

    public function ban(User $user, Request $request): JsonResponse
    {
        $user->revokePermissionTo(Permission::CreatePost);
        $user->revokePermissionTo(Permission::MakeComment);
        $user->revokePermissionTo(Permission::ReactToPost);

        return response()->json(
            ["message" => "$user->name successfully banned"],
            Status::HTTP_OK,
        );
    }
}
