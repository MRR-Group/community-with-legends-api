<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $resolved_at
 * @property User $user
 * @property User|Post|Comment $reportable
 */
class Report extends Model
{
    protected $fillable = ["reason", "user_id", "resolved_at"];
    protected $casts = [
        "resolved_at" => "date",
    ];

    public function reportable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
