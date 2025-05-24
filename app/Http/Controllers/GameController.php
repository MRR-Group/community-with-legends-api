<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Resources\GameResource;
use CommunityWithLegends\Jobs\ImportGamesFromJSON;
use CommunityWithLegends\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GameController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $filter = $request->input("filter");

        if ($filter) {
            $games = Game::query()
                ->whereRaw("LOWER(name) LIKE ?", ["%" . strtolower($filter) . "%"])
                ->paginate(20);
        } else {
            $games = Game::query()->paginate(20);
        }

        return GameResource::collection($games)->response();
    }

    public function index(Request $request): JsonResponse
    {
        $games = Game::query()->paginate(20);

        return GameResource::collection($games)->response();
    }

    public function import()
    {
        if (Cache::get("game_import_in_progress")) {
            return response()->json([
                "status" => "error",
                "message" => "An import is already in progress!",
            ]);
        }

        dispatch(new ImportGamesFromJSON());

        return response()->json([
            "status" => "ok",
            "message" => "Import has been started.",
        ]);
    }

    public function getProgress()
    {
        if (!Cache::get("game_import_in_progress")) {
            return response()->json([
                "status" => "done",
                "progress" => 100,
            ]);
        }

        $progress = Cache::get("game_import_progress", 0);

        return response()->json([
            "status" => "in_progress",
            "progress" => $progress,
        ]);
    }
}
