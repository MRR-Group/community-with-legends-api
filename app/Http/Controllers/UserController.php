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
        $oldName = $user->name;

        $user->name = $updateNicknameRequest->validated()["name"];

        $user->save();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log("User changed nickname from '{$oldName}' to '{$user->name}'");

        return response()->json(
            ["message" => __("user.nickname_changed")],
            Status::HTTP_OK,
        );
    }

    public function ban(User $user, Request $request): JsonResponse
    {
        if ($user->reports->isEmpty()) {
            $user->reports()->save(new Report(["user_id" => auth()->id()]));
        }

        if ($user->hasRole([Role::Moderator, Role::Administrator, Role::SuperAdministrator])) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Blocked ban attempt on privileged user '{$user->name}'");

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

        $banned_for = $duration === null ? "permanently banned" : "banned for {$duration} days";

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("User '{$user->name}' was $banned_for. Banned by IP: " . ($by_ip ? "yes" : "no"));

        return response()->json(
            ["message" => __("user.banned", ["name" => $user->name])],
            Status::HTTP_OK,
        );
    }

    public function unban(User $user, Request $request): JsonResponse
    {
        $user->unban();

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->log("User '{$user->name}' was unbanned");

        return response()->json(
            ["message" => __("user.unbanned", ["name" => $user->name])],
            Status::HTTP_OK,
        );
    }

    public function deleteAvatar(Request $request, IdenticonHelper $identiconHelper): JsonResponse
    {
        $identiconHelper->create($request->user()->id, $request->user()->email);

        activity()
            ->causedBy(auth()->user())
            ->performedOn(auth()->user())
            ->log("User deleted their avatar");

        return response()->json(
            ["message" => __("user.avatar_deleted")],
            Status::HTTP_OK,
        );
    }

    public function forceAvatarChange(User $user, IdenticonHelper $identiconHelper): JsonResponse
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator])) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Blocked forced avatar change on '{$user->name}' due to high role");

            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $identiconHelper->create($user->id, $user->email);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Admin forced avatar change for user '{$user->name}'");

        return response()->json(
            ["message" => __("user.avatar_changed", ["name" => $user->name])],
            Status::HTTP_OK,
        );
    }

    public function forceNameChange(User $user, Request $request): JsonResponse
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator])) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Blocked forced name change on '{$user->name}' due to high role");

            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $oldName = $user->name;

        $user->name = "Renamed User";
        $user->save();

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->log("Forced name change from '{$oldName}' to 'Renamed User'");

        return response()->json(
            ["message" => __("user.name_changed", ["oldName" => $oldName])],
            Status::HTTP_OK,
        );
    }

    public function anonymize(User $user, IdenticonHelper $identiconHelper): JsonResponse
    {
        if ($user->hasRole([Role::Moderator, Role::Administrator, Role::SuperAdministrator])) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Blocked anonymization attempt on privileged user '{$user->name}'");

            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        foreach ($user->permissions() as $permission) {
            $user->revokePermissionTo($permission);
        }

        $oldName = $user->name;

        $user->email = "$user->id@anonymous.com";
        $user->name = "Anonymous";
        $user->save();

        $identiconHelper->create($user->id, $user->email);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("User '{$oldName}' was anonymized");

        return response()->json(
            ["message" => __("user.anonymized", ["name" => $user->name])],
            Status::HTTP_OK,
        );
    }

    public function grantModeratorPrivileges(User $user): JsonResponse
    {
        $user->assignRole(Role::Moderator->value);
        $user->givePermissionTo(Role::Moderator->permissions());

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Granted moderator privileges to '{$user->name}'");

        return response()->json(
            ["message" => __("user.moderator_granted", ["name" => $user->name])],
            Status::HTTP_OK,
        );
    }

    public function revokeModeratorPrivileges(User $user): JsonResponse
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator]) || !$user->hasRole(Role::Moderator)) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Blocked revocation of moderator role on '{$user->name}'");

            return response()->json(
                [],
                Status::HTTP_FORBIDDEN,
            );
        }

        $user->removeRole(Role::Moderator->value);
        $user->revokePermissionTo(Role::Moderator->permissions());
        $user->givePermissionTo(Role::User->permissions());

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Revoked moderator privileges from '{$user->name}'");

        return response()->json(
            ["message" => __("user.moderator_revoked", ["name" => $user->name])],
            Status::HTTP_OK,
        );
    }

    public function setPassword(SetPasswordRequest $setPasswordRequest): JsonResponse
    {
        $user = $setPasswordRequest->user();

        if ($user->hasPassword) {
            return response()->json(
                ["message" => __("user.password_set")],
                Status::HTTP_CONFLICT,
            );
        }

        $password = $setPasswordRequest->validated()["password"];
        $user->password = Hash::make($password);
        $user->save();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log("User {$user->name} set password");

        return response()->json([
            ["message" => __("user.already_has_password")],
        ], Status::HTTP_OK);
    }
}
