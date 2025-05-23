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
        $credentials = $loginRequest->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                "message" => "The provided credentials do not match our records.",
            ], Status::HTTP_FORBIDDEN);
        }

        $user = Auth::user();
        $token = $user->createToken("api-token")->plainTextToken;

        return response()->json([
            "message" => "success",
            "token" => $token,
            "user_id" => $user->id,
        ], Status::HTTP_OK);
    }

    public function refresh(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            "message" => "success",
            "user_id" => $user->id,
        ], Status::HTTP_OK);
    }
}
