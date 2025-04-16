<?php

declare(strict_types=1);

namespace CommunityWithLegends\Enums;



use Illuminate\Support\Collection;

enum Permission: string
{
    case CreatePost = "createPost";
    case MakeComment = "makeComment";
    case ReactToPost = "reactToPost";
    case BanUsers = "banUsers";
    case ViewUsers = "viewUsers";
    case DeletePosts = "deletePosts";
    case ManageAdministrators = "manageAdministrators";
    case ManageModerators = "manageModerators";

    public static function toBoolean(Collection|array $permissions): array
    {
        $result = [];

        foreach ($permissions as $permission) {
            $key = "can" . ucfirst($permission);

            $result[$key] = true;
        }

        return $result;
    }
}
