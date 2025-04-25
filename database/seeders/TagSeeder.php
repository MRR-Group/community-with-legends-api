<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        DB::table("tags")->insertOrIgnore([
            [
                "name" => "Clutch moment",
            ],
            [
                "name" => "Funny moment",
            ],
            [
                "name" => "Glitch",
            ],
        ]);
    }
}
