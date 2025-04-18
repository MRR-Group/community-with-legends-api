<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("tags")->insert([
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
