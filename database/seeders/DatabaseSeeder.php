<?php

declare(strict_types=1);

namespace Database\Seeders;

use CommunityWithLegends\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(PermissionsSeeder::class);

        User::factory([
            "email" => "admin@cwl.com",
        ])->admin()->create();
    }
}
