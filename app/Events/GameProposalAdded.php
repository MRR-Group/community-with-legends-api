<?php

declare(strict_types=1);

namespace CommunityWithLegends\Events;

use CommunityWithLegends\Models\GameProposal;
use CommunityWithLegends\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameProposalAdded implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public GameProposal $proposal,
        public User $target,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user." . $this->target->id),
        ];
    }

    public function broadcastAs(): string
    {
        return "game.proposal.added";
    }

    public function broadcastWhen(): bool
    {
        return $this->proposal->user->isNot($this->target);
    }

    public function broadcastWith(): array
    {
        return [
            "id" => $this->proposal->id,
            "game_title" => $this->proposal->game->name,
            "game_id" => $this->proposal->game->id,
            "user" => $this->proposal->user->name,
            "created_at" => $this->proposal->created_at->toDateTimeString(),
        ];
    }
}
