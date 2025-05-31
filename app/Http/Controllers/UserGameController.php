<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\UserGameRequest;
use CommunityWithLegends\Http\Resources\UserGameResource;
use CommunityWithLegends\Models\User;
use CommunityWithLegends\Models\UserGame;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as Status;

class UserGameController extends Controller
{
    public function index(User $user): JsonResponse
    {
        $games = $user->userGames()->with("game")->get();

        return UserGameResource::collection($games)->response();
    }

    public function show(UserGame $userGame): JsonResponse
    {
        return UserGameResource::make($userGame)->response();
    }

    public function store(UserGameRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        if ($user->userGames()->where("game_id", $data["game_id"])->exists()) {
            return response()->json([
                "message" => "You already have this game in your list.",
            ], Status::HTTP_CONFLICT);
        }

        $userGame = UserGame::create([
            "user_id" => $user->id,
            "game_id" => $data["game_id"],
            "status" => $data["status"],
        ]);

        return response()->json([
            "message" => "Game has been added",
            "id" => $userGame->id,
        ], Status::HTTP_CREATED);
    }

    public function update(UserGame $userGame, UserGameRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($userGame->user->isNot($user)) {
            return response()->json([
                "message" => "Forbidden",
            ], Status::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $userGame->update([
            "status" => $data["status"],
        ]);

        return response()->json(["Game has been edited"], Status::HTTP_OK);
    }

    public function destroy(UserGame $userGame): JsonResponse
    {
        $user = auth()->user();

        if ($userGame->user->isNot($user)) {
            return response()->json([
                "message" => "Forbidden",
            ], Status::HTTP_FORBIDDEN);
        }

        $userGame->delete();

        return response()->json([
            "message" => "User game entry deleted successfully.",
        ], Status::HTTP_OK);
    }
}
