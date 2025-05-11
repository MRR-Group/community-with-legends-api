<?php

declare(strict_types=1);

use CommunityWithLegends\Enums\Permission;
use CommunityWithLegends\Enums\Role;
use Spatie\Permission\DefaultTeamResolver;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;

return [
    "models" => [
        "permission" => SpatiePermission::class,
        "role" => SpatieRole::class,
    ],

    "table_names" => [
        "roles" => "roles",
        "permissions" => "permissions",
        "model_has_permissions" => "model_has_permissions",
        "model_has_roles" => "model_has_roles",
        "role_has_permissions" => "role_has_permissions",
    ],

    "column_names" => [
        "role_pivot_key" => null,
        "permission_pivot_key" => null,
        "model_morph_key" => "model_id",
        "team_foreign_key" => "team_id",
    ],

    "register_permission_check_method" => true,
    "register_octane_reset_listener" => false,
    "events_enabled" => false,
    "teams" => false,
    "team_resolver" => DefaultTeamResolver::class,
    "use_passport_client_credentials" => false,
    "display_permission_in_exception" => false,
    "display_role_in_exception" => false,
    "enable_wildcard_permission" => false,

    "cache" => [
        "expiration_time" => DateInterval::createFromDateString("24 hours"),
        "key" => "spatie.permission.cache",
        "store" => "default",
    ],

    "permissions" => [
        Permission::CreatePost->value,
        Permission::MakeComment->value,
        Permission::ReactToPost->value,
        Permission::BanUsers->value,
        Permission::AnonymizeUsers->value,
        Permission::ViewUsers->value,
        Permission::DeletePosts->value,
        Permission::ManageAdministrators->value,
        Permission::ManageModerators->value,
    ],
    "permission_roles" => [
        Role::SuperAdministrator->value => [
            Permission::CreatePost->value,
            Permission::MakeComment->value,
            Permission::ReactToPost->value,
            Permission::ViewUsers->value,
            Permission::AnonymizeUsers->value,
            Permission::BanUsers->value,
            Permission::DeletePosts->value,
            Permission::ManageModerators->value,
            Permission::ManageAdministrators->value,
        ],
        Role::Administrator->value => [
            Permission::CreatePost->value,
            Permission::MakeComment->value,
            Permission::ReactToPost->value,
            Permission::ViewUsers->value,
            Permission::AnonymizeUsers->value,
            Permission::BanUsers->value,
            Permission::DeletePosts->value,
            Permission::ManageModerators->value,
        ],
        Role::Moderator->value => [
            Permission::CreatePost->value,
            Permission::MakeComment->value,
            Permission::ReactToPost->value,
            Permission::ViewUsers->value,
            Permission::BanUsers->value,
            Permission::DeletePosts->value,
        ],
        Role::User->value => [
            Permission::CreatePost->value,
            Permission::MakeComment->value,
            Permission::ReactToPost->value,
        ],
    ],
];
