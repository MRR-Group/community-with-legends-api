<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    protected $fillable = ["user_id", "game_id", "tag_id", "content"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function asset(): HasOne
    {
        return $this->hasOne(PostAsset::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function reactionsCount()
    {
        return $this->reactions()->count();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
