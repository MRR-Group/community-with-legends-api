<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers\Auth;

use Carbon\Carbon;
use CommunityWithLegends\Http\Controllers\Controller;
use CommunityWithLegends\Http\Requests\TFARequest;
use CommunityWithLegends\Models\UserTfaCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as Status;

class TFAController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        if (!$this->isFromTrustedMobileClient($request)) {
            return response()->json(["error" => __("auth.forbidden")], Status::HTTP_FORBIDDEN);
        }

        $user = auth()->user();
        $user->userTfaCode()?->delete();

        $plainCode = (string)random_int(100000, 999999);

        $tfa = new UserTfaCode();
        $tfa->user_id = $user->id;
        $tfa->code = Hash::make($plainCode);

        $tfa->expires_at = Carbon::now()->addMinutes((int)config("auth.tfa.expiration_time"));
        $tfa->save();

        return response()->json([
            "message" => "success",
            "token" => $plainCode,
            "expires_at" => $tfa->expires_at,
        ], Status::HTTP_OK);
    }

    public function validate(TFARequest $request): JsonResponse
    {
        $user = auth()->user();
        $tfa = $user->userTfaCode;

        if ($user->userTfaCode === null) {
            return response()->json(["error" => __("auth.tfa_no_active_code")], Status::HTTP_BAD_REQUEST);
        }

        if (!Hash::check($request->validated(["token"]), $tfa->code)) {
            return response()->json(["error" => __("auth.tfa_invalid_code")], Status::HTTP_UNAUTHORIZED);
        }

        if ($tfa->expires_at->isPast()) {
            return response()->json(["error" => __("auth.tfa_expired_code")], Status::HTTP_UNAUTHORIZED);
        }

        $tfa->delete();
        $request->session()->put("tfa_passed", true);

        return response()->json([
            "message" => __("auth.tfa_success"),
            "user_id" => $user->id,
        ], Status::HTTP_OK);
    }

    private function isFromTrustedMobileClient(Request $request): bool
    {
        return $request->header("X-Client-Platform") === "mobile";
    }
}
