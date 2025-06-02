<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureTfaPassed
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(["error" => __("auth.unauthorized")], 401);
        }

        if (!$request->session()->get("tfa_passed")) {
            return response()->json(["error" => __("auth.tfa_required")], 403);
        }

        return $next($request);
    }
}
