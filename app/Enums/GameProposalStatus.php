<?php

declare(strict_types=1);

namespace CommunityWithLegends\Enums;

enum GameProposalStatus: string
{
    case Pending = "pending";
    case Accepted = "accepted";
    case Rejected = "rejected";
}
