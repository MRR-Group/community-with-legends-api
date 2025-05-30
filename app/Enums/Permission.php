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
    case ManageReports = "manageReports";
    case RenameUsers = "renameUsers";
    case ChangeUsersAvatar = "changeUsersAvatar";
    case DeleteUserHardware = "deleteUserHardware";
    case AnonymizeUsers = "anonymizeUsers";
    case ManageAdministrators = "manageAdministrators";
    case ManageModerators = "manageModerators";
    case UpdateGames = "updateGames";
}
