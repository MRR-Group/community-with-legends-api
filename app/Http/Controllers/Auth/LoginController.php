<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers\Auth;

use CommunityWithLegends\Http\Controllers\Controller;
use CommunityWithLegends\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as Status;

class LoginController extends Controller
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
        $token = $user->createToken("api-token")->plainTextToken;

        return response()->json([
            "message" => "success",
            "token" => $token,
            "user_id" => $user->id,
        ], Status::HTTP_OK);
    }
}
