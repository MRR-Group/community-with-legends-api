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
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection<PostAsset> $postAssets
 */
class AssetType extends Model
{
    protected $fillable = ["name"];

    public function postAssets(): HasMany
    {
        return $this->hasMany(PostAsset::class);
    }
}
