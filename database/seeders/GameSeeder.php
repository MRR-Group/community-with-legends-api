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
                "name" => "R.E.P.O",
            ],
            [
                "name" => "Supermarket Together",
            ],
            [
                "name" => "Papers, Please",
            ],
            [
                "name" => "The Binding of Isaac: Rebirth",
            ],
            [
                "name" => "Counter-Strike 2",
            ],
        ]);
    }
}
