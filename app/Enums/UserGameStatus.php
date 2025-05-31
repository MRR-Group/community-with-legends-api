<?php

namespace CommunityWithLegends\Enums;

enum UserGameStatus: string
{
    case ToPlay = 'to_play';
    case Playing = 'playing';
    case Played = 'played';
}
