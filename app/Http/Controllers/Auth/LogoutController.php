<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers\Auth;

use CommunityWithLegends\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response as Status;

class LogoutController extends Controller
{
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return response()->json([
            "message" => "Logged out",
        ], Status::HTTP_OK);
    }
}
