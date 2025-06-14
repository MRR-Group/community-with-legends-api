<?php

declare(strict_types=1);

use CommunityWithLegends\Enums\Permission;
use CommunityWithLegends\Http\Controllers\AdministratorController;
use CommunityWithLegends\Http\Controllers\Auth\LoginController;
use CommunityWithLegends\Http\Controllers\Auth\LogoutController;
use CommunityWithLegends\Http\Controllers\Auth\RegisterController;
use CommunityWithLegends\Http\Controllers\Auth\TFAController;
use CommunityWithLegends\Http\Controllers\ChangeAvatarController;
use CommunityWithLegends\Http\Controllers\CommentController;
use CommunityWithLegends\Http\Controllers\GameController;
use CommunityWithLegends\Http\Controllers\HardwareController;
use CommunityWithLegends\Http\Controllers\LogController;
use CommunityWithLegends\Http\Controllers\PostController;
use CommunityWithLegends\Http\Controllers\ReportController;
use CommunityWithLegends\Http\Controllers\ResetPasswordController;
use CommunityWithLegends\Http\Controllers\StatisticsController;
use CommunityWithLegends\Http\Controllers\TagController;
use CommunityWithLegends\Http\Controllers\TwitchController;
use CommunityWithLegends\Http\Controllers\UserController;
use CommunityWithLegends\Http\Controllers\UserGameController;
use CommunityWithLegends\Http\Controllers\UserProposalController;
use CommunityWithLegends\Http\Middleware\EnsureTfaPassed;
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

Route::middleware(["auth:sanctum", "logout.banned"])->group(function (): void {
    Route::post("/auth/logout", [LogoutController::class, "logout"]);
    Route::post("/auth/refresh", [LoginController::class, "refresh"])->name("refresh");

    Route::get("/user", [UserController::class, "getCurrentUser"]);
    Route::post("/user/name", [UserController::class, "changeName"]);
    Route::post("/user/avatar", [ChangeAvatarController::class, "store"]);
    Route::delete("/user/avatar", [UserController::class, "deleteAvatar"]);
    Route::post("/user/set-password", [UserController::class, "setPassword"]);

    Route::post("/users/{user}/ban", [UserController::class, "ban"])->middleware(Authorize::using(Permission::BanUsers));
    Route::post("/users/{user}/unban", [UserController::class, "unban"])->middleware(Authorize::using(Permission::BanUsers));

    Route::delete("/users/{user}/avatar", [UserController::class, "forceAvatarChange"])->middleware(Authorize::using(Permission::ChangeUsersAvatar));
    Route::delete("/users/{user}/name", [UserController::class, "forceNameChange"])->middleware(Authorize::using(Permission::RenameUsers));
    Route::post("/users/{user}/anonymize", [UserController::class, "anonymize"])->middleware(Authorize::using(Permission::AnonymizeUsers));
    Route::delete("/users/{user}/hardware", [HardwareController::class, "forceDeleteAll"])->middleware(Authorize::using(Permission::DeleteUserHardware));
    Route::post("/users/{user}/games/{game}/propose", [UserProposalController::class, "store"]);

    Route::post("/user-games", [UserGameController::class, "store"]);
    Route::post("/user-games/{userGame}", [UserGameController::class, "update"]);
    Route::delete("/user-games/{userGame}", [UserGameController::class, "destroy"]);

    Route::delete("/proposals/{gameProposal}", [UserProposalController::class, "destroy"]);
    Route::post("/proposals/{gameProposal}/accept", [UserProposalController::class, "accept"]);
    Route::post("/proposals/{gameProposal}/reject", [UserProposalController::class, "reject"]);
    Route::post("/proposals/{gameProposal}/like", [UserProposalController::class, "like"]);
    Route::post("/proposals/{gameProposal}/dislike", [UserProposalController::class, "dislike"]);
    Route::delete("/proposals/{gameProposal}/like", [UserProposalController::class, "removeReaction"]);

    Route::post("/users/{user}/grant-moderator-privileges", [UserController::class, "grantModeratorPrivileges"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::ManageModerators)]);
    Route::post("/users/{user}/revoke-moderator-privileges", [UserController::class, "revokeModeratorPrivileges"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::ManageModerators)]);

    Route::get("/hardware/{item}", [HardwareController::class, "show"]);
    Route::post("/user/hardware", [HardwareController::class, "store"]);
    Route::post("/user/hardware/{item}", [HardwareController::class, "update"]);
    Route::delete("/user/hardware/{item}", [HardwareController::class, "destroy"]);

    Route::get("/admins", [AdministratorController::class, "index"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::ManageAdministrators)]);
    Route::post("/admins", [AdministratorController::class, "store"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::ManageAdministrators)]);
    Route::delete("/admins/{user}", [AdministratorController::class, "delete"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::ManageAdministrators)]);
    Route::post("/admins/{user}/revoke-administrator-privileges", [AdministratorController::class, "revokeAdministratorPrivileges"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::ManageAdministrators)]);

    Route::post("/posts", [PostController::class, "store"])->middleware(Authorize::using(Permission::CreatePost));
    Route::delete("/posts/{post}", [PostController::class, "remove"])->middleware(Authorize::using(Permission::DeletePosts));
    Route::post("/posts/{post}/restore", [PostController::class, "restorePost"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::DeletePosts)])->withTrashed();
    Route::post("/posts/{post}/reactions", [PostController::class, "addReaction"])->middleware(Authorize::using(Permission::ReactToPost));
    Route::delete("/posts/{post}/reactions", [PostController::class, "removeReaction"])->middleware(Authorize::using(Permission::ReactToPost));
    Route::post("/posts/{post}/comments", [CommentController::class, "store"])->middleware(Authorize::using(Permission::MakeComment));
    Route::delete("/comments/{comment}", [PostController::class, "removeComment"])->middleware(Authorize::using(Permission::DeletePosts));
    Route::post("/comments/{comment}/restore", [PostController::class, "restoreComment"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::DeletePosts)])->withTrashed();

    Route::post("/posts/{post}/report", [ReportController::class, "storePost"]);
    Route::post("/comments/{comment}/report", [ReportController::class, "storeComment"]);
    Route::post("/users/{user}/report", [ReportController::class, "storeUser"]);

    Route::get("/reports", [ReportController::class, "index"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::BanUsers), Authorize::using(Permission::ManageReports)]);
    Route::get("/reports/posts", [ReportController::class, "indexPosts"])->middleware(EnsureTfaPassed::class, Authorize::using(Permission::ManageReports));
    Route::get("/reports/comments", [ReportController::class, "indexComments"])->middleware(EnsureTfaPassed::class, Authorize::using(Permission::ManageReports));
    Route::get("/reports/users", [ReportController::class, "indexUsers"])->middleware(EnsureTfaPassed::class, Authorize::using(Permission::ManageReports));

    Route::post("/reports/{report}/reopen", [ReportController::class, "reopen"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::BanUsers), Authorize::using(Permission::ManageReports)]);
    Route::post("/reports/{report}/close", [ReportController::class, "close"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::BanUsers), Authorize::using(Permission::ManageReports)]);

    Route::post("/games/import", [GameController::class, "import"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::UpdateGames)]);
    Route::get("/games/import/progress", [GameController::class, "getProgress"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::UpdateGames)]);

    Route::get("/logs", [LogController::class, "index"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::ViewLogs)]);
    Route::get("/statistics", [StatisticsController::class, "index"])->middleware([EnsureTfaPassed::class, Authorize::using(Permission::ViewLogs)]);

    Route::get("/auth/tfa", [LoginController::class, "refresh"])->middleware([EnsureTfaPassed::class]);
    Route::post("/auth/tfa/generate", [TFAController::class, "generate"]);
    Route::post("/auth/tfa/validate", [TFAController::class, "validate"]);
});

Route::group([], function (): void {
    Route::post("/auth/login", [LoginController::class, "login"])->name("login");
    Route::post("/auth/register", [RegisterController::class, "register"]);

    Route::post("/auth/forgot-password", [ResetPasswordController::class, "sendResetLinkEmail"]);
    Route::post("/auth/reset-password", [ResetPasswordController::class, "reset"]);

    Route::get("/users", [UserController::class, "index"]);
    Route::get("/users/search", [UserController::class, "search"]);
    Route::get("/users/{user}", [UserController::class, "show"]);
    Route::get("/users/{user}/posts", [PostController::class, "indexByUser"]);
    Route::get("/users/{user}/hardware", [HardwareController::class, "index"]);
    Route::get("/users/{user}/games", [UserGameController::class, "index"]);
    Route::get("/users/{user}/proposals", [UserProposalController::class, "index"]);

    Route::get("/user-games/{userGame}", [UserGameController::class, "show"]);
    Route::get("/proposals/{gameProposal}", [UserProposalController::class, "show"]);

    Route::get("/twitch/auth/login/{platform}", [TwitchController::class, "loginByAuthCode"]);
    Route::get("/twitch/auth/register/{platform}", [TwitchController::class, "registerByAuthCode"]);
    Route::get("/twitch/token", [TwitchController::class, "receiveAccessToken"]);

    Route::get("/posts", [PostController::class, "index"]);
    Route::get("/posts/trending", [PostController::class, "getTrendingPosts"]);
    Route::get("/posts/filter", [PostController::class, "getFilteredPosts"]);
    Route::get("/posts/{post}", [PostController::class, "show"]);
    Route::get("/comments/{comment}", [CommentController::class, "show"]);

    Route::get("/games", [GameController::class, "index"]);
    Route::get("/games/search", [GameController::class, "search"]);

    Route::get("/tags", [TagController::class, "index"]);
    Route::get("/tags/search", [TagController::class, "search"]);
});
