<?php

declare(strict_types=1);

namespace Database\Factories;

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            "name" => fake()->name(),
            "email" => fake()->unique()->safeEmail(),
            "email_verified_at" => now(),
            "password" => Hash::make("password"),
            "remember_token" => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(Role::User);
            $user->syncPermissions(Role::User->permissions());
        });
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes): array => [
            "email_verified_at" => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(Role::Administrator);
            $user->syncPermissions(Role::Administrator->permissions());
        });
    }

    public function superAdmin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(Role::SuperAdministrator);
            $user->syncPermissions(Role::SuperAdministrator->permissions());
        });
    }

    public function moderator(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(Role::Moderator);
            $user->syncPermissions(Role::Moderator->permissions());
        });
    }
}
