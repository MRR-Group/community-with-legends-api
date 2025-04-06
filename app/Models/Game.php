<?php

namespace CommunityWithLegends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = ['name'];

    public function posts(): hasMany
    {
        return $this->hasMany(Post::class);
    }
}
