<?php

declare(strict_types=1);

namespace CommunityWithLegends\Console\Commands;

use CommunityWithLegends\Enums\Role as RoleEnum;
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
}
