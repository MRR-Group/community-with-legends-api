<?php

namespace CommunityWithLegends\Models;

use Carbon\Carbon;
use CommunityWithLegends\Enums\GameProposalVoteType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $game_proposal_id
 * @property int $value
 * @property GameProposalVoteType $vote_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property GameProposal $gameProposal
 */
class GameProposalVote extends Model
{
    protected $fillable = [
        'user_id',
        'game_proposal_id',
        'vote_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(GameProposal::class, 'game_proposal_id');
    }

    protected function value(): Attribute
    {
        return Attribute::get(function (): int {
            return $this->vote_type === GameProposalVoteType::Up ? 1 : -1;
        });
    }

    protected function casts(): array
    {
        return [
            'vote_type' => GameProposalVoteType::class,
        ];
    }
}
