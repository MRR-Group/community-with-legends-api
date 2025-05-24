<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Post $post
 * @property User $user
 * @property ?Carbon $deleted_at
 * @property Collection<Report> $reports
 */
class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ["post_id", "user_id", "content"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, "reportable");
    }
}
