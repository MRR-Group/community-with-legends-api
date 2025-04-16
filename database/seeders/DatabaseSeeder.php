<?php

declare(strict_types=1);

namespace Database\Seeders;

use CommunityWithLegends\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(GameSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(AssetTypesSeeder::class);

        if (User::query()->where("email", "=", "admin@cwl.com")->count() === 0) {
            User::factory([
                "email" => "admin@cwl.com",
                "password" => env("DEFAULT_SUPER_ADMIN_PASSWORD", "admin"),
            ])->superAdmin()->create();
        }
    }
}
