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
}
