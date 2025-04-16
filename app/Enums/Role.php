<?php

declare(strict_types=1);

namespace CommunityWithLegends\Enums;

enum Role: string
{
    case User = "user";
    case SuperAdministrator = "superAdministrator";
    case Administrator = "administrator";
    case Moderator = "moderator";

    public static function casesToSelect(): array
    {
        $cases = collect(Role::cases());

        return $cases->map(
            fn(Role $enum): array => [
                "label" => $enum->label(),
                "value" => $enum->value,
            ],
        )->toArray();
    }

    public function label(): string
    {
        return __($this->value);
    }

    public function permissions(): array
    {
        return config("permission.permission_roles")[$this->value];
    }
}
