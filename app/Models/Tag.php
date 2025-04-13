<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    protected $fillable = ["name"];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
