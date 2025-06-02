<?php

declare(strict_types=1);

namespace CommunityWithLegends\Console\Commands;

use CommunityWithLegends\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MergeTags extends Command
{
    protected $signature = "tags:merge";
    protected $description = "Find and merge duplicate tags, reassigning all related records to a single tag";

    public function handle(): int
    {
        $tags = Tag::all();
        $processed = collect([]);

        $this->info("Starting to merge duplicate tags...");

        $totalTags = $tags->count();
        $processedTagsCount = 0;

        foreach ($tags as $tag) {
            if ($processed->contains($tag->name)) {
                $duplicates = Tag::query()->where("name", $tag->name)->get();

                if ($duplicates->count() <= 1) {
                    continue;
                }

                $this->info("Processing tag: {$tag->name} with {$duplicates->count()} duplicates.");

                $this->mergeTags($duplicates);
                $processed->add($tag->name);
                $processedTagsCount++;
                $this->line("Processed {$processedTagsCount}/{$totalTags} tags.");
            }
        }

        $this->info("Merge completed! Processed {$processedTagsCount}/{$totalTags} tags.");

        return self::SUCCESS;
    }

    private function mergeTags(Collection $duplicates): void
    {
        $first = $duplicates->first();

        foreach ($duplicates as $duplicate) {
            if ($duplicate->id !== $first->id) {
                $this->reassignTagPostsToMainTag($duplicate, $first);
                $duplicate->delete();
            }
        }
    }

    private function reassignTagPostsToMainTag(Tag $tag, Tag $mainTag): void
    {
        DB::table("post_tag")
            ->where("tag_id", $tag->id)
            ->update(["tag_id" => $mainTag->id]);
    }
}
