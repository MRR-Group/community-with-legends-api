<?php

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAsset extends Model
{
    protected $fillable = ['post_id', 'type_id'];

    public function post(): belongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function type(): belongsTo
    {
        return $this->belongsTo(AssetType::class);
    }
}
