<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAsset extends Model
{
    protected $fillable = ["post_id", "type_id", "link"];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }
}
