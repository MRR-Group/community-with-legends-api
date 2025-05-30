<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Resources;

use CommunityWithLegends\Models\Comment;
use CommunityWithLegends\Models\Post;
use CommunityWithLegends\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "reason" => $this->reason,
            "reportable_type" => class_basename($this->reportable_type),
            "reportable" => $this->resolveReportableResource(),
            "reported_at" => $this->created_at->toDateTimeString(),
            "resolved_at" => optional($this->resolved_at)?->toDateTimeString(),
            "status" => $this->resolveStatus(),
            "reported_by" => new UserResource($this->whenLoaded("user")),
        ];
    }

    protected function resolveReportableResource()
    {
        return match ($this->reportable_type) {
            Post::class => new PostResource($this->reportable),
            Comment::class => new CommentResource($this->reportable),
            User::class => new UserResource($this->reportable),
            default => null,
        };
    }

    protected function resolveStatus(): array
    {
        $statuses = [];

        if ($this->resolved_at === null) {
            return ["pending"];
        }

        if ($this->resolved_at) {
            $statuses[] = "resolved";
        }

        $reportable = $this->reportable;

        if ($this->reportable_type === User::class && $reportable?->isBanned) {
            $statuses[] = "user_banned";

            return $statuses;
        }

        $author = $reportable?->user;

        if ($author && empty($author->isBanned)) {
            $statuses[] = "user_banned";
        }

        if ($this->reportable_type !== User::class && $reportable?->trashed()) {
            $statuses[] = "deleted";
        }

        return $statuses;
    }
}
