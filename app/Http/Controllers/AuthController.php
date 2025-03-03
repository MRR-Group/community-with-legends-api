<?php

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\LoginRequest;
use CommunityWithLegends\Http\Requests\RegisterRequest;
use CommunityWithLegends\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as Status;

class AuthController extends Controller
{
    public function login(LoginRequest $loginRequest): \Illuminate\Http\JsonResponse
    {
        if (Auth::attempt($loginRequest->validated())) {
            $loginRequest->session()->regenerate();

            return  response()->json([
                "message" => "success",
            ])->setStatusCode(Status::HTTP_OK);
        }

        return response()->json([
            "message" => "The provided credentials do not match our records.",
        ])->setStatusCode(Status::HTTP_FORBIDDEN);
    }
    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        if (Auth::user()) {
            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return  response()->json([
                "message" => "success",
            ])->setStatusCode(Status::HTTP_OK);
        }

        return response()->json([
            "message" => "You are not logged in.",
        ])->setStatusCode(Status::HTTP_UNAUTHORIZED);
    }
    public function register(RegisterRequest $registerRequest)
    {
        $validated = $registerRequest->validated();
        $userExist = User::query()->where('email', $validated['email'])->exists();

        if(!$userExist){
            $user = new User($validated);
            $user->password = Hash::make($validated['password']);
            $user->save();

            Auth::login($user);
        }

        return response()->json([
            "message" => "success",
        ])->setStatusCode(Status::HTTP_OK);
    }

}
