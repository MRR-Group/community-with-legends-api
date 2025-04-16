<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("asset_types")->insert([
            [
                "id" => 1,
                "name" => "Image",
            ],
            [
                "id" => 2,
                "name" => "Video",
            ],
        ]);
    }
}
