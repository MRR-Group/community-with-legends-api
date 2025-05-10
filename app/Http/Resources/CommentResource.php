<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
        ];
    }
}
