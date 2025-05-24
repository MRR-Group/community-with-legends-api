<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $cover
 * @property string $twitch_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection<Post> $posts
 */
class Game extends Model
{
    protected $fillable = ["name", "cover", "twitch_id"];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
