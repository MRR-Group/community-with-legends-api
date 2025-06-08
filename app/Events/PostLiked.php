<?php

declare(strict_types=1);

namespace CommunityWithLegends\Events;

use CommunityWithLegends\Models\Post;
use CommunityWithLegends\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLiked implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Post $post,
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
        return "post.liked";
    }

    public function broadcastWhen(): bool
    {
        return $this->post->reactions->count() % 10 === 0;
    }

    public function broadcastWith(): array
    {
        return [
            "post_id" => $this->post->id,
            "likes" => $this->post->reactions->count(),
        ];
    }
}
