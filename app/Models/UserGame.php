<?php

declare(strict_types=1);

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use CommunityWithLegends\Enums\UserGameStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $game_id
 * @property UserGameStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property Game $game
 */
class UserGame extends Model
{
    protected $fillable = ["user_id", "game_id", "status"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    protected function casts(): array
    {
        return [
            "status" => UserGameStatus::class,
        ];
    }
}
