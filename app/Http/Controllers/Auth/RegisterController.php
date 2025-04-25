<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers\Auth;

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Http\Controllers\Controller;
use CommunityWithLegends\Http\Requests\RegisterRequest;
use CommunityWithLegends\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as Status;

class RegisterController extends Controller
{
    public function register(RegisterRequest $registerRequest)
    {
        $validated = $registerRequest->validated();
        $userExist = User::query()->where("email", $validated["email"])->exists();

        if (!$userExist) {
            $user = new User($validated);
            $user->password = Hash::make($validated["password"]);
            $user->save();

            $user->assignRole(Role::User);
            $user->syncPermissions(Role::User->permissions());
        }

        return response()->json([
            "message" => "success",
        ])->setStatusCode(Status::HTTP_CREATED);
    }
}
