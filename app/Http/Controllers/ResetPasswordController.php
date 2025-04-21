<?php

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\PasswordResetLinkRequest;
use CommunityWithLegends\Http\Requests\ResetPasswordRequest;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        return response()->json([
            "message" => "If the email is correct, a link to reset your password will be sent to it",
        ], Status::HTTP_OK);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $resetEntry = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$resetEntry || !Hash::check($validated['token'], $resetEntry->token)) {
            return response()->json([
                "message" => "Invalid code",
            ], Status::HTTP_BAD_REQUEST);
        }

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();
        $user->forceFill([
           'password' => Hash::make($validated['password']),
           'remember_token' => Str::random(60),
        ]);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return response()->json([
            "message" => "Password successfully reset",
        ], Status::HTTP_OK);

    }
}
