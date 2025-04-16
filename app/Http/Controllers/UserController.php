<?php

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Resources\UserResource;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;

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
}
