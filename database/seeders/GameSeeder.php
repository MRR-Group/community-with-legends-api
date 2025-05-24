<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        DB::table("games")->insertOrIgnore([
            [
                "twitch_id" => "1294658629",
                "name" => "R.E.P.O",
                "cover" => "https://static-cdn.jtvnw.net/ttv-boxart/1294658629_IGDB-236x322.jpg",
            ],
            [
                "twitch_id" => "942317893",
                "name" => "Supermarket Together",
                "cover" => "https://static-cdn.jtvnw.net/ttv-boxart/942317893_IGDB-236x322.jpg",
            ],
            [
                "twitch_id" => "133897",
                "name" => "Papers, Please",
                "cover" => "https://static-cdn.jtvnw.net/ttv-boxart/133897_IGDB-236x322.jpg",
            ],
            [
                "twitch_id" => "94073",
                "name" => "The Binding of Isaac: Rebirth",
                "cover" => "https://static-cdn.jtvnw.net/ttv-boxart/94073_IGDB-236x322.jpg",
            ],
            [
                "twitch_id" => "495359",
                "name" => "Counter-Strike 2",
                "cover" => "https://static-cdn.jtvnw.net/ttv-boxart/495359_IGDB-236x322.jpg",
            ],
        ]);
    }
}
