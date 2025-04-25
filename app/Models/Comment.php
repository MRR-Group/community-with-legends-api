<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Post $post
 * @property User $user
 */
class Comment extends Model
{
    protected $fillable = ["post_id", "user_id", "content"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
