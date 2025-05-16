<?php

declare(strict_types=1);

use CommunityWithLegends\Enums\Permission;
use CommunityWithLegends\Http\Controllers\Auth\LoginController;
use CommunityWithLegends\Http\Controllers\Auth\LogoutController;
use CommunityWithLegends\Http\Controllers\Auth\RegisterController;
use CommunityWithLegends\Http\Controllers\ChangeAvatarController;
use CommunityWithLegends\Http\Controllers\CommentController;
use CommunityWithLegends\Http\Controllers\GameController;
use CommunityWithLegends\Http\Controllers\PostController;
use CommunityWithLegends\Http\Controllers\ResetPasswordController;
use CommunityWithLegends\Http\Controllers\TagController;
use CommunityWithLegends\Http\Controllers\TwitchController;
use CommunityWithLegends\Http\Controllers\UserController;
use CommunityWithLegends\Models\User;
use Illuminate\Auth\Middleware\Authorize;
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
    Route::post("/auth/logout", [LogoutController::class, "logout"]);

    Route::get("/user", fn(Request $request) => $request->user());
    Route::get("/users", [UserController::class, "index"])->middleware(Authorize::using(Permission::ViewUsers));
    Route::get("/users/{user}", [UserController::class, "show"]);
    Route::post("/users/{user}/ban", [UserController::class, "ban"])->middleware(Authorize::using(Permission::BanUsers));
    Route::post("/users/{user}/unban", [UserController::class, "unban"])->middleware(Authorize::using(Permission::BanUsers));
    Route::post("/users/{user}/anonymize", [UserController::class, "anonymize"])->middleware(Authorize::using(Permission::AnonymizeUsers));
    Route::post("/users/{user}/grant-moderator-privileges", [UserController::class, "grantModeratorPrivileges"])->middleware(Authorize::using(Permission::ManageModerators));
    Route::post("/users/{user}/revoke-moderator-privileges", [UserController::class, "revokeModeratorPrivileges"])->middleware(Authorize::using(Permission::ManageModerators));
    Route::post("/users/{user}/revoke-administrator-privileges", [UserController::class, "revokeAdministratorPrivileges"])->middleware(Authorize::using(Permission::ManageAdministrators));

    Route::post("/avatar", [ChangeAvatarController::class, "store"]);

    Route::post("/posts", [PostController::class, "store"])->middleware(Authorize::using(Permission::CreatePost));
    Route::post("/posts/{post}/reactions", [PostController::class, "addReaction"])->middleware(Authorize::using(Permission::ReactToPost));
    Route::delete("/posts/{post}/reactions", [PostController::class, "removeReaction"])->middleware(Authorize::using(Permission::DeletePosts));
    Route::post("/posts/{post}/comments", [CommentController::class, "store"])->middleware(Authorize::using(Permission::MakeComment));
});

Route::group([], function (): void {
    Route::post("/auth/login", [LoginController::class, "login"])->name("login");
    Route::post("/auth/register", [RegisterController::class, "register"]);

    Route::post("/auth/forgot-password", [ResetPasswordController::class, "sendResetLinkEmail"]);
    Route::post("/auth/reset-password", [ResetPasswordController::class, "reset"]);

    Route::get("/twitch/auth/login/{platform}", [TwitchController::class, "loginByAuthCode"]);
    Route::get("/twitch/auth/register/{platform}", [TwitchController::class, "registerByAuthCode"]);
    Route::get("/twitch/token", [TwitchController::class, "receiveAccessToken"]);

    Route::get("/posts", [PostController::class, "index"]);
    Route::get("/posts/trending", [PostController::class, "getTrendingPosts"]);
    Route::get("/posts/filter", [PostController::class, "getFilteredPosts"]);
    Route::get("/posts/{post}", [PostController::class, "show"]);

    Route::get("/games", [GameController::class, "index"]);
    Route::get("/games/search", [GameController::class, "search"]);

    Route::get("/tags", [TagController::class, "index"]);
    Route::get("/tags/search", [TagController::class, "search"]);
});
