<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Http\Requests\HardwareItemRequest;
use CommunityWithLegends\Http\Resources\HardwareItemResource;
use CommunityWithLegends\Models\HardwareItem;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as Status;

class HardwareController extends Controller
{
    public function index(User $user): JsonResponse
    {
        return HardwareItemResource::collection($user->hardware)->response();
    }

    public function show(HardwareItem $item): JsonResponse
    {
        return HardwareItemResource::make($item)->response();
    }

    public function store(HardwareItemRequest $request)
    {
        $user = $request->user();
        $item = $user->hardware()->create($request->validated());

        activity()
            ->causedBy($user)
            ->performedOn($item)
            ->log("Created hardware item: " . $item->title);

        return response()->json($item, Status::HTTP_CREATED);
    }

    public function update(HardwareItemRequest $request, HardwareItem $item)
    {
        $user = $request->user();

        if ($item->user->isNot($user)) {
            activity()
                ->causedBy($user)
                ->performedOn($item)
                ->log("Unauthorized attempt to update hardware item: " . $item->title);

            return response()->json(["message" => __("hardware.unauthorized")], Status::HTTP_FORBIDDEN);
        }

        $item->update($request->validated());

        activity()
            ->causedBy($user)
            ->performedOn($item)
            ->log("Updated hardware item: " . $item->title);

        return HardwareItemResource::make($item)->response();
    }

    public function destroy(HardwareItem $item, Request $request)
    {
        $user = $request->user();

        if ($item->user->isNot($user)) {
            activity()
                ->causedBy($user)
                ->performedOn($item)
                ->log("Unauthorized attempt to delete hardware item: " . $item->title);

            return response()->json(["message" => __("hardware.unauthorized")], Status::HTTP_FORBIDDEN);
        }

        $item->delete();

        activity()
            ->causedBy($user)
            ->performedOn($item)
            ->log("Deleted hardware item: " . $item->title);

        return response()->json(
            [
                "message" => __("hardware.deleted", ["title" => $item->title]),
            ],
        );
    }

    public function forceDeleteAll(User $user)
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator])) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Unauthorized attempt to delete all hardware items for user: " . $user->name);

            return response()->json([], Status::HTTP_FORBIDDEN);
        }

        $user->hardware()->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Deleted all hardware items for user: " . $user->name);

        return response()->json(["message" => __("hardware.all_deleted")]);
    }
}
