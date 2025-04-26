<?php

declare(strict_types=1);

namespace CommunityWithLegends\Enums;

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
}
