<?php

declare(strict_types=1);

use CommunityWithLegends\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::post("/auth/token", function (Request $request) {
    $request->validate([
        "email" => "required|email",
        "password" => "required",
        "device_name" => "required",
    ]);

    $user = User::query()->where("email", $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            "email" => ["The provided credentials are incorrect."],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});

Route::get("/user", fn(Request $request) => $request->user())->middleware("auth:sanctum");
