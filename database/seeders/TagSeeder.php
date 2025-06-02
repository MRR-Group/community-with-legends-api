<?php

declare(strict_types=1);

namespace Database\Seeders;

use CommunityWithLegends\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            "Clutch moment", "Epic fail", "Glitch", "Highlight", "Best play", "No scope", "Headshot", "Speedrun",
            "Epic comeback", "Sneaky move", "Pro moment", "Fail", "Bug", "Cheese", "Victory", "Teamplay", "Solo play",
            "Trap", "Game breaker", "WTF moment", "Epic kill", "Strategy", "Surprise", "Skill", "Hilarious moment",
            "Fail compilation", "Crazy", "Rage quit", "Clutch play", "Streamer moment", "Community clip", "Game reaction",
            "Epic save", "Funny glitch", "Highlight reel", "Boss fight", "Multikill", "Sneak attack", "Epic troll",
            "Funny fail", "Victory dance", "Funny reaction", "Lag spike", "Clutch clutch", "Game glitch", "Funny moment",
            "Replay", "Speedrun fail", "Epic combo", "News", "Screenshot", "Opinion", "Discussion", "Controversial",
            "Review", "Guide", "Update", "Event", "Announcement",
        ];

        foreach ($tags as $tagName) {
            Tag::query()->firstOrCreate(["name" => $tagName]);
        }
    }
}
