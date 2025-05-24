<?php

declare(strict_types=1);

namespace CommunityWithLegends\Jobs;

use CommunityWithLegends\Models\Game;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ImportGamesFromJSON implements ShouldQueue
{
    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;

    public $tries = 1;

    public function handle(): void
    {
        $jsonUrl = "https://api.twitchinsights.net/v1/game/all";
        $response = Http::get($jsonUrl);

        if ($response->failed()) {
            Cache::forget("game_import_in_progress");
            Cache::forget("game_import_progress");

            return;
        }

        $games = $response->json()["games"];
        $total = count($games);
        $processed = 0;

        Cache::put("game_import_in_progress", true);

        foreach ($games as $game) {
            $coverUrl = $game["logourl"] ?? "";
            $coverUrl = preg_replace('/-\d+x\d+\.jpg$/', "-236x322.jpg", $coverUrl);

            Game::query()->updateOrCreate(
                ["twitch_id" => $game["id"]],
                [
                    "name" => $game["game"],
                    "cover" => $coverUrl,
                ],
            );

            $processed++;
            $progress = intval($processed / $total * 100);

            Cache::put("game_import_progress", $progress);
        }

        Cache::forget("game_import_in_progress");
        Cache::forget("game_import_progress");
    }
}
