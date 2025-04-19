<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostTag extends Model
{
    protected $fillable = ["post_id", "tag_id"];
    protected $table = "post_tags";

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function tag(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
