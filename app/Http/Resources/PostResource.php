<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "content" => $this->content,
            "created_at" => $this->created_at,
            "user" => UserResource::make($this->user),
            "game" => GameResource::make($this->game),
            "tags" => TagResource::collection($this->tags),
            "asset" => PostAssetResource::make($this->asset),
            "reactions" => $this->reactions->count(),
            "user_reacted" => $request->user() ? $this->reactions->contains("user_id", $request->user()->id) : false,
            "comments" => CommentResource::collection($this->comments),
        ];
    }
}
