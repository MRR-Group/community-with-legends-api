<?php

declare(strict_types=1);

namespace CommunityWithLegends\Console\Commands;

use CommunityWithLegends\Enums\Role as RoleEnum;
use CommunityWithLegends\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    protected $signature = "permissions:sync";
    protected $description = "Synchronize permissions and roles with config and enums";

    public function handle(): int
    {
        $this->info("Syncing permissions...");
        $this->syncPermissions();

        $this->info("Syncing roles and assigning permissions...");
        $this->syncRolesAndAssignments();

        $this->info("Syncing user roles...");
        $this->syncUsersRoles();
        $this->syncUserPermissionsBasedOnRoles();

        $this->info("Permissions & Roles synchronized successfully.");

        return self::SUCCESS;
    }

    protected function syncPermissions(): void
    {
        $definedPermissions = collect(config("permission.permissions"))->unique();
        $existingPermissions = Permission::pluck("name");

        $definedPermissions->diff($existingPermissions)->each(function ($perm): void {
            Permission::create(["name" => $perm]);
            $this->line("Created permission: $perm");
        });

        $existingPermissions->diff($definedPermissions)->each(function ($perm): void {
            Permission::where("name", $perm)->delete();
            $this->line("Deleted obsolete permission: $perm");
        });
    }

    protected function syncUsersRoles(): void
    {
        User::all()->each(function (User $user): void {
            $email = $user->email;

            $role = match (true) {
                $email === "admin@cwl.com" => RoleEnum::SuperAdministrator->value,
                str_starts_with($email, "admin") && preg_match('/^admin\d+@cwl\.com$/', $email) => RoleEnum::Administrator->value,
                default => RoleEnum::User->value,
            };

            $user->syncRoles([$role]);
            $this->line("User {$user->id} ({$email}) assigned to role: {$role}");
        });
    }

    protected function syncRolesAndAssignments(): void
    {
        $rolePermissionsMap = config("permission.permission_roles");

        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::firstOrCreate(["name" => $roleEnum->value]);
            $this->line("Ensuring role: {$roleEnum->value}");

            $permissions = $rolePermissionsMap[$roleEnum->value] ?? [];

            $role->syncPermissions($permissions);
            $this->line("Assigned " . count($permissions) . " permission(s)");
        }
    }

    protected function syncUserPermissionsBasedOnRoles(): void
    {
        User::with("roles")->each(function (User $user): void {
            $role = $user->roles->first();

            if (!$role) {
                $this->warn("User {$user->id} has no role assigned. Skipping...");

                return;
            }

            $roleEnum = RoleEnum::tryFrom($role->name);

            if (!$roleEnum) {
                $this->warn("User {$user->id} has unknown role '{$role->name}'. Skipping...");

                return;
            }

            $permissions = $roleEnum->permissions();
            $user->syncPermissions($permissions);

            $this->line("User {$user->id} assigned " . count($permissions) . " permission(s) based on role '{$role->name}'");
        });
    }
}
