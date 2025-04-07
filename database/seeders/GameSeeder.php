<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('games')->insert([
            [
                'name' => "R.E.P.O"
            ],
            [
                'name' => "Supermarket Together"
            ],
            [
                'name' => "Papers, Please"
            ],
            [
                'name' => "The Binding of Isaac: Rebirth"
            ],
            [
                'name' => "Counter-Strike 2"
            ],
        ]);
    }
}
