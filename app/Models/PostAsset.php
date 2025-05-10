<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $link
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Post $post
 * @property AssetType $type
 */
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
