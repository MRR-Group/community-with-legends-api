<?php

declare(strict_types=1);

use CommunityWithLegends\Http\Controllers\Auth\LoginController;
use CommunityWithLegends\Http\Controllers\Auth\LogoutController;
use CommunityWithLegends\Http\Controllers\Auth\RegisterController;
use CommunityWithLegends\Http\Controllers\GameController;
use CommunityWithLegends\Http\Controllers\PostController;
use CommunityWithLegends\Http\Controllers\ResetPasswordController;
use CommunityWithLegends\Http\Controllers\TagController;
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

Route::middleware("auth:sanctum")->group(function (): void {
    Route::get("/user", fn(Request $request) => $request->user());
    Route::get("/games", [GameController::class, "index"]);
    Route::get("/games/search", [GameController::class, "search"]);
    Route::get("/tags", [TagController::class, "index"]);
    Route::get("/tags/search", [TagController::class, "search"]);

    Route::post("/posts", [PostController::class, "store"]);
    Route::post("/posts/{id}/reactions", [PostController::class, "addReaction"]);
    Route::delete("/posts/{id}/reactions", [PostController::class, "removeReaction"]);
    Route::get("/posts", [PostController::class, "index"]);

    Route::post("/auth/logout", [LogoutController::class, "logout"]);
});

Route::post("/auth/login", [LoginController::class, "login"])->name("login");
Route::post("/auth/register", [RegisterController::class, "register"]);
Route::post("/auth/forgot-password", [ResetPasswordController::class, "sendResetLinkEmail"]);
Route::post("/auth/reset-password", [ResetPasswordController::class, "reset"]);
