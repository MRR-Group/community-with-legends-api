<?php

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $fillable = ['post_id', 'user_id'];

    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): belongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
