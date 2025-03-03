<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\LoginRequest;
use CommunityWithLegends\Http\Requests\RegisterRequest;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as Status;

class AuthController extends Controller
{
    public function login(LoginRequest $loginRequest): JsonResponse
    {
        if (!Auth::attempt($loginRequest->validated())) {
            return response()->json([
                "message" => "The provided credentials do not match our records.",
            ], Status::HTTP_FORBIDDEN);
        }

        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            "message" => "success",
            "token" => $token
        ], Status::HTTP_OK);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logged out"
        ], Status::HTTP_OK);
    }

    public function register(RegisterRequest $registerRequest)
    {
        $validated = $registerRequest->validated();
        $userExist = User::query()->where("email", $validated["email"])->exists();

        if (!$userExist) {
            $user = new User($validated);
            $user->password = Hash::make($validated["password"]);
            $user->save();
        }

        return response()->json([
            "message" => "success",
        ])->setStatusCode(Status::HTTP_OK);
    }
}
