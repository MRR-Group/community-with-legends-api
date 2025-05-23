<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 * @property User $user
 * @property ?Game $game
 * @property ?PostAsset $asset
 * @property Collection<Reaction> $reactions
 * @property Collection<Comment> $comments
 * @property Collection<Tag> $tags
 * @property Collection<Report> $reports
 */
class Post extends Model
{
    use SoftDeletes;

    protected $fillable = ["user_id", "game_id", "content"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function asset(): HasOne
    {
        return $this->hasOne(PostAsset::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy("created_at", "desc");
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, "reportable");
    }
}
