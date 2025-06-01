<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use Carbon\Carbon;
use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Helpers\IdenticonHelper;
use CommunityWithLegends\Http\Requests\SetPasswordRequest;
use CommunityWithLegends\Http\Requests\UpdateNicknameRequest;
use CommunityWithLegends\Http\Resources\UserResource;
use CommunityWithLegends\Models\Report;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as Status;

class UserController
{
    public function index(): JsonResponse
    {
        return UserResource::collection(User::withoutRole([Role::Administrator, Role::SuperAdministrator])->get())->response();
    }

    public function show(User $user): JsonResponse
    {
        return UserResource::make($user)->response();
    }

    public function getCurrentUser(Request $request): JsonResponse
    {
        return UserResource::make($request->user())->response();
    }

    public function search(Request $request): JsonResponse
    {
        $filter = $request->input("filter");

        if ($filter) {
            $filter = strtolower($filter);
        }

        $users = User::query()
            ->whereRaw("LOWER(name) LIKE ?", ["%" . $filter . "%"])
            ->get();

        return UserResource::collection($users)->response();
    }

    public function changeName(UpdateNicknameRequest $updateNicknameRequest): JsonResponse
    {
        $user = $updateNicknameRequest->user();

        $user->name = $updateNicknameRequest->validated()["name"];

        $user->save();

        return response()->json(
            ["message" => "Nickname successfully changed"],
            Status::HTTP_OK,
        );
    }

    public function ban(User $user, Request $request): JsonResponse
    {
        if ($user->reports->isEmpty()) {
            $user->reports()->save(new Report(["user_id" => auth()->id()]));
        }

        if ($user->hasRole([Role::Moderator, Role::Administrator, Role::SuperAdministrator])) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $duration = $request->integer("duration", null);
        $by_ip = $request->boolean("by_ip");

        $user->ban([
            "ip" => $by_ip ? $user->last_login_ip : null,
            "expired_at" => $duration !== null ? Carbon::now()->addDays($duration) : null,
        ]);

        return response()->json(
            ["message" => "$user->name successfully banned"],
            Status::HTTP_OK,
        );
    }

    public function unban(User $user, Request $request): JsonResponse
    {
        $user->unban();

        return response()->json(
            ["message" => "$user->name successfully unbanned"],
            Status::HTTP_OK,
        );
    }

    public function deleteAvatar(Request $request, IdenticonHelper $identiconHelper): JsonResponse
    {
        $identiconHelper->create($request->user()->id, $request->user()->email);

        return response()->json(
            ["message" => "Avatar successfully deleted"],
            Status::HTTP_OK,
        );
    }

    public function forceAvatarChange(User $user, IdenticonHelper $identiconHelper): JsonResponse
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator])) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $identiconHelper->create($user->id, $user->email);

        return response()->json(
            ["message" => "$user->name's avatar successfully changed"],
            Status::HTTP_OK,
        );
    }

    public function forceNameChange(User $user, Request $request): JsonResponse
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator])) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $oldName = $user->name;

        $user->name = "Renamed User";
        $user->save();

        return response()->json(
            ["message" => "$oldName's name successfully changed"],
            Status::HTTP_OK,
        );
    }

    public function anonymize(User $user, IdenticonHelper $identiconHelper): JsonResponse
    {
        if ($user->hasRole([Role::Moderator, Role::Administrator, Role::SuperAdministrator])) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        foreach ($user->permissions() as $permission) {
            $user->revokePermissionTo($permission);
        }

        $user->email = "$user->id@anonymous.com";
        $user->name = "Anonymous";
        $user->save();

        $identiconHelper->create($user->id, $user->email);

        return response()->json(
            ["message" => "$user->name successfully anonymized"],
            Status::HTTP_OK,
        );
    }

    public function grantModeratorPrivileges(User $user): JsonResponse
    {
        $user->assignRole(Role::Moderator->value);
        $user->givePermissionTo(Role::Moderator->permissions());

        return response()->json(
            ["message" => "Moderator privileges granted to $user->name"],
            Status::HTTP_OK,
        );
    }

    public function revokeModeratorPrivileges(User $user): JsonResponse
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator]) || !$user->hasRole(Role::Moderator)) {
            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $user->removeRole(Role::Moderator->value);
        $user->revokePermissionTo(Role::Moderator->permissions());
        $user->givePermissionTo(Role::User->permissions());

        return response()->json(
            ["message" => "Moderator privileges revoked from $user->name"],
            Status::HTTP_OK,
        );
    }

    public function setPassword(SetPasswordRequest $setPasswordRequest): JsonResponse
    {
        $user = $setPasswordRequest->user();

        if ($user->hasPassword) {

            return response()->json(
                ["message" => "The user already has a password set."],
                Status::HTTP_CONFLICT,
            );
        }

        $password = $setPasswordRequest['password'];
        $user->password = Hash::make($password);
        $user->save();

        return response()->json([
            'message' => 'Password has been set successfully.'
        ], Status::HTTP_OK);
    }
}
