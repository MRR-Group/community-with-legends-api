<?php

declare(strict_types=1);

namespace CommunityWithLegends\Events;

use CommunityWithLegends\Models\Comment;
use CommunityWithLegends\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Comment $comment,
        public User $target,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user." . $this->target->id),
        ];
    }

    public function broadcastAs(): string
    {
        return "proposal.added";
    }

    public function broadcastWith(): array
    {
        return [
            "post_id" => $this->comment->post->id,
            "comment_id" => $this->comment->id,
            "author_name" => $this->comment->user->name,
            "created_at" => $this->comment->created_at->toDateTimeString(),
        ];
    }
}
