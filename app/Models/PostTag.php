<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $post_id
 * @property int $tag_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Post $post
 * @property Tag $tag
 */
class PostTag extends Model
{
    protected $fillable = ["post_id", "tag_id"];
    protected $table = "post_tags";

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function tag(): BelongsTo
    {
        return $this->BelongsTo(Tag::class);
    }
}
