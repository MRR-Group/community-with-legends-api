<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\PasswordResetLinkRequest;
use CommunityWithLegends\Http\Requests\ResetPasswordRequest;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as Status;

class ResetPasswordController extends Controller
{
    public function sendResetLinkEmail(PasswordResetLinkRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Password::sendResetLink($validated);

        activity()
            ->withProperties(["email" => $validated["email"]])
            ->log("Requested a password reset link");

        return response()->json([
            "message" => __("password_reset.link_sent"),
        ], Status::HTTP_OK);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $resetEntry = DB::table("password_reset_tokens")
            ->where("email", $validated["email"])
            ->first();

        if (!$resetEntry || !Hash::check($validated["token"], $resetEntry->token)) {
            activity()
                ->withProperties(["email" => $validated["email"]])
                ->log("Attempted to reset password with invalid token");

            return response()->json([
                "message" => __("password_reset.invalid_token"),
            ], Status::HTTP_BAD_REQUEST);
        }

        $user = User::query()
            ->where("email", $validated["email"])
            ->first();
        $user->forceFill([
            "password" => Hash::make($validated["password"]),
            "remember_token" => Str::random(60),
        ]);
        $user->save();

        DB::table("password_reset_tokens")->where("email", $validated["email"])->delete();

        activity()
            ->performedOn($user)
            ->log("Reset their password");

        return response()->json([
            "message" => __("password_reset.success"),
        ], Status::HTTP_OK);
    }
}
