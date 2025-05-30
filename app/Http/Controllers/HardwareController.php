<?php

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Http\Controllers\Controller;
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

        return response()->json($item, Status::HTTP_CREATED);
    }

    public function update(HardwareItemRequest $request, HardwareItem $item)
    {
        $user = $request->user();

        if ($item->user->isNot($user)) {
            return response()->json(['message' => 'Unauthorized'], Status::HTTP_FORBIDDEN);
        }

        $item->update($request->validated());

        return HardwareItemResource::make($item)->response();
    }

    public function destroy(HardwareItem $item, Request $request)
    {
        $user = $request->user();

        if ($item->user->isNot($user)) {
            return response()->json(['message' => 'Unauthorized'], Status::HTTP_FORBIDDEN);
        }

        $item->delete();

        return response()->json(['message' => "$item->title deleted"]);
    }

    public function forceDeleteAll(User $user)
    {
        if ($user->hasRole([Role::Administrator, Role::SuperAdministrator])) {
            return response()->json([], Status::HTTP_FORBIDDEN);
        }

        $user->hardware()->delete();

        return response()->json(['message' => 'All hardware deleted']);
    }
}
