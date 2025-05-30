<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 */
class HardwareItem extends Model
{
    protected $fillable = ['user_id', 'title', 'value'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
