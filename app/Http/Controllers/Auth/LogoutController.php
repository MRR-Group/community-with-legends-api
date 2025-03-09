<?php

namespace CommunityWithLegends\Http\Controllers\Auth;

use CommunityWithLegends\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as Status;

class LogoutController extends Controller
{
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logged out"
        ], Status::HTTP_OK);
    }
}
