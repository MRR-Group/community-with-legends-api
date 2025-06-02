<?php

declare(strict_types=1);

namespace Database\Seeders;

use CommunityWithLegends\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        Tag::query()->firstOrCreate(["name" => "Clutch moment"]);
        Tag::query()->firstOrCreate(["name" => "Epic fail"]);
        Tag::query()->firstOrCreate(["name" => "Glitch"]);
        Tag::query()->firstOrCreate(["name" => "Highlight"]);
        Tag::query()->firstOrCreate(["name" => "Best play"]);
        Tag::query()->firstOrCreate(["name" => "No scope"]);
        Tag::query()->firstOrCreate(["name" => "Headshot"]);
        Tag::query()->firstOrCreate(["name" => "Speedrun"]);
        Tag::query()->firstOrCreate(["name" => "Epic comeback"]);
        Tag::query()->firstOrCreate(["name" => "Sneaky move"]);
        Tag::query()->firstOrCreate(["name" => "Pro moment"]);
        Tag::query()->firstOrCreate(["name" => "Fail"]);
        Tag::query()->firstOrCreate(["name" => "Bug"]);
        Tag::query()->firstOrCreate(["name" => "Cheese"]);
        Tag::query()->firstOrCreate(["name" => "Victory"]);
        Tag::query()->firstOrCreate(["name" => "Teamplay"]);
        Tag::query()->firstOrCreate(["name" => "Solo play"]);
        Tag::query()->firstOrCreate(["name" => "Trap"]);
        Tag::query()->firstOrCreate(["name" => "Game breaker"]);
        Tag::query()->firstOrCreate(["name" => "WTF moment"]);
        Tag::query()->firstOrCreate(["name" => "Epic kill"]);
        Tag::query()->firstOrCreate(["name" => "Strategy"]);
        Tag::query()->firstOrCreate(["name" => "Surprise"]);
        Tag::query()->firstOrCreate(["name" => "Skill"]);
        Tag::query()->firstOrCreate(["name" => "Hilarious moment"]);
        Tag::query()->firstOrCreate(["name" => "Fail compilation"]);
        Tag::query()->firstOrCreate(["name" => "Crazy"]);
        Tag::query()->firstOrCreate(["name" => "Rage quit"]);
        Tag::query()->firstOrCreate(["name" => "Clutch play"]);
        Tag::query()->firstOrCreate(["name" => "Streamer moment"]);
        Tag::query()->firstOrCreate(["name" => "Community clip"]);
        Tag::query()->firstOrCreate(["name" => "Game reaction"]);
        Tag::query()->firstOrCreate(["name" => "Epic save"]);
        Tag::query()->firstOrCreate(["name" => "Funny glitch"]);
        Tag::query()->firstOrCreate(["name" => "Highlight reel"]);
        Tag::query()->firstOrCreate(["name" => "Boss fight"]);
        Tag::query()->firstOrCreate(["name" => "Multikill"]);
        Tag::query()->firstOrCreate(["name" => "Sneak attack"]);
        Tag::query()->firstOrCreate(["name" => "Epic troll"]);
        Tag::query()->firstOrCreate(["name" => "Funny fail"]);
        Tag::query()->firstOrCreate(["name" => "Victory dance"]);
        Tag::query()->firstOrCreate(["name" => "Funny reaction"]);
        Tag::query()->firstOrCreate(["name" => "Lag spike"]);
        Tag::query()->firstOrCreate(["name" => "Clutch clutch"]);
        Tag::query()->firstOrCreate(["name" => "Game glitch"]);
        Tag::query()->firstOrCreate(["name" => "Funny moment"]);
        Tag::query()->firstOrCreate(["name" => "Replay"]);
        Tag::query()->firstOrCreate(["name" => "Speedrun fail"]);
        Tag::query()->firstOrCreate(["name" => "Epic combo"]);
        Tag::query()->firstOrCreate(["name" => "News"]);
        Tag::query()->firstOrCreate(["name" => "Screenshot"]);
        Tag::query()->firstOrCreate(["name" => "Opinion"]);
        Tag::query()->firstOrCreate(["name" => "Discussion"]);
        Tag::query()->firstOrCreate(["name" => "Controversial"]);
        Tag::query()->firstOrCreate(["name" => "Review"]);
        Tag::query()->firstOrCreate(["name" => "Guide"]);
        Tag::query()->firstOrCreate(["name" => "Update"]);
        Tag::query()->firstOrCreate(["name" => "Event"]);
        Tag::query()->firstOrCreate(["name" => "Announcement"]);
    }
}
