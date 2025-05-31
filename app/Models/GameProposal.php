<?php

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use CommunityWithLegends\Enums\GameProposalStatus;
use CommunityWithLegends\Enums\UserGameStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $target_user_id
 * @property int $game_id
 * @property GameProposalStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $votesCount
 *
 * @property User $user
 * @property User $targetUser
 * @property Game $game
 * @property Collection<GameProposalVote> $votes
 */
class GameProposal extends Model
{
    protected $fillable = [
        'user_id',
        'target_user_id',
        'game_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(GameProposalVote::class);
    }

    protected function votesCount(): Attribute
    {
        return Attribute::get(fn() => $this->votes->sum('value'));
    }

    protected function casts(): array
    {
        return [
            'status' => GameProposalStatus::class,
        ];
    }

    public function scopeAlreadyProposed($query, int $userId, int $targetUserId, int $gameId)
    {
        return $query->where('user_id', $userId)
            ->where('target_user_id', $targetUserId)
            ->where('game_id', $gameId);
    }

    public static function hasUserAlreadyProposed(int $userId, int $targetUserId, int $gameId): bool
    {
        return self::alreadyProposed($userId, $targetUserId, $gameId)->exists();
    }

    public static function userHasGame(User $user, int $gameId): bool
    {
        return $user->userGames()
            ->where('game_id', $gameId)
            ->whereIn('status', [
                UserGameStatus::Played->value,
                UserGameStatus::Playing->value,
                UserGameStatus::ToPlay->value
            ])
            ->exists();
    }
}
