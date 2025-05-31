<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userVote = null;
        $user = auth()->user();

        if ($user) {
            $vote = $this->votes->firstWhere("user_id", $user->id);
            $userVote = $vote !== null;
        }

        return [
            "id" => $this->id,
            "user" => UserResource::make($this->user),
            "targetUser" => UserResource::make($this->targetUser),
            "game" => GameResource::make($this->game),
            "status" => $this->status,
            "created_at" => $this->created_at,
            "votes" => $this->votesCount,
            "user_vote" => $userVote,
        ];
    }
}
