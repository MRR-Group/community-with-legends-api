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
            activity()
                ->causedBy($user)
                ->log("Attempted to add an already assigned game with game_id: {$data["game_id"]}");

            $userGame = $user->userGames()->where("game_id", $data["game_id"])->first();
            $userGame->status = $data["status"];
            $userGame->save();            

            activity()
                ->causedBy($user)
                ->performedOn($userGame)
                ->log("Updated UserGame ID {$userGame->id} with new status: {$data["status"]}");

            return response()->json([
                "message" => __("user_game.edited"),
            ], Status::HTTP_OK);
        }

        $userGame = UserGame::create([
            "user_id" => $user->id,
            "game_id" => $data["game_id"],
            "status" => $data["status"],
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($userGame)
            ->log("Added a new game with game_id: {$data["game_id"]} and status: {$data["status"]}");

        return response()->json([
            "message" => __("user_game.added"),
            "id" => $userGame->id,
        ], Status::HTTP_CREATED);
    }

    public function update(UserGame $userGame, UserGameRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($userGame->user->isNot($user)) {
            activity()
                ->causedBy($user)
                ->performedOn($userGame)
                ->log("Unauthorized attempt to update UserGame ID {$userGame->id}");

            return response()->json([
                "message" => __("user_game.forbidden"),
            ], Status::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $userGame->update([
            "status" => $data["status"],
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($userGame)
            ->log("Updated UserGame ID {$userGame->id} with new status: {$data["status"]}");

        return response()->json([
            "message" => __("user_game.edited"),
        ], Status::HTTP_OK);
    }

    public function destroy(UserGame $userGame): JsonResponse
    {
        $user = auth()->user();

        if ($userGame->user->isNot($user)) {
            activity()
                ->causedBy($user)
                ->performedOn($userGame)
                ->log("Unauthorized attempt to delete UserGame ID {$userGame->id}");

            return response()->json([
                "message" => __("user_game.forbidden"),
            ], Status::HTTP_FORBIDDEN);
        }

        $userGame->delete();

        activity()
            ->causedBy($user)
            ->performedOn($userGame)
            ->log("Deleted UserGame ID {$userGame->id}");

        return response()->json([
            "message" => __("user_game.deleted"),
        ], Status::HTTP_OK);
    }
}
