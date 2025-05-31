<?php

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Enums\GameProposalStatus;
use CommunityWithLegends\Enums\UserGameStatus;
use CommunityWithLegends\Http\Resources\GameProposalResource;
use CommunityWithLegends\Models\Game;
use CommunityWithLegends\Models\GameProposal;
use CommunityWithLegends\Models\GameProposalVote;
use CommunityWithLegends\Models\User;
use CommunityWithLegends\Models\UserGame;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as Status;

class UserProposalController extends Controller
{
    public function index(User $user): JsonResponse
    {
        $proposals = GameProposal::with(['user', 'targetUser', 'game', 'votes'])
            ->where('target_user_id', $user->id)
            ->whereNot('status', GameProposalStatus::Accepted->value)
            ->get();

        return GameProposalResource::collection($proposals)->response();
    }

    public function show(GameProposal $proposal): JsonResponse
    {
        return GameProposalResource::make($proposal)->response();
    }

    public function store(User $user, Game $game): JsonResponse
    {
        $authUser = auth()->user();

        if ($user->is($authUser)) {
            return response()->json([
                "message" => "Cannot propose a game to yourself"
            ], Status::HTTP_BAD_REQUEST);
        }

        if (GameProposal::hasUserAlreadyProposed($authUser->id, $user->id, $game->id)) {
            return response()->json([
                "message" => "Proposal already exists"
            ], Status::HTTP_CONFLICT);
        }

        if (GameProposal::userHasGame($user, $game->id)) {
            return response()->json([
                "message" => "User already has this game"
            ], Status::HTTP_CONFLICT);
        }

        $proposal = new GameProposal([
            'user_id' => $authUser->id,
            'target_user_id' => $user->id,
            'game_id' => $game->id,
        ]);

        $proposal->save();

        return response()->json([
            "message" => "Proposal has been sent"
        ], Status::HTTP_CREATED);
    }

    public function destroy(GameProposal $proposal): JsonResponse
    {
        if ($proposal->user->isNot(auth()->user())) {
            return response()->json(['message' => 'Unauthorized'], Status::HTTP_FORBIDDEN);
        }

        $proposal->delete();

        return response()->json([
            "message" => "Proposal has been deleted"
        ], Status::HTTP_OK);
    }

    public function accept(GameProposal $proposal): JsonResponse
    {
        $user = auth()->user();

        if ($proposal->targetUser->isNot($user)) {
            return response()->json(['message' => 'Unauthorized'], Status::HTTP_FORBIDDEN);
        }

        $proposal->status = GameProposalStatus::Accepted;
        $proposal->save();

        $userGame = UserGame::query()->create([
            'user_id' => $user->id,
            'game_id' => $proposal->game->id,
            'status' => UserGameStatus::ToPlay,
        ]);

        return response()->json([
            "message" => "Proposal has been accepted",
            "userGameId" => $userGame->id,
        ], Status::HTTP_CREATED);
    }

    public function reject(GameProposal $proposal): JsonResponse
    {
        $user = auth()->user();

        if ($proposal->targetUser->isNot($user)) {
            return response()->json(['message' => 'Unauthorized'], Status::HTTP_FORBIDDEN);
        }

        $proposal->status = GameProposalStatus::Rejected;
        $proposal->save();

        return response()->json([
            "message" => "Proposal has been rejected",
        ], Status::HTTP_OK);
    }

    public function like(GameProposal $proposal): JsonResponse
    {
        $user = auth()->user();

        if ($proposal->user->is($user) && $proposal->targetUser->is($user)) {
            return response()->json(['message' => 'Unauthorized'], Status::HTTP_FORBIDDEN);
        }

        $vote = GameProposalVote::query()->firstOrNew([
            'user_id' => $user->id,
            'game_proposal_id' => $proposal->id,
        ]);

        $vote->vote_type = 'up';
        $vote->value = 1;
        $vote->save();

        return response()->json([
            "message" => "Proposal has been liked",
        ], Status::HTTP_OK);
    }

    public function dislike(GameProposal $proposal): JsonResponse
    {
        $user = auth()->user();

        if ($proposal->user->is($user) && $proposal->targetUser->is($user)) {
            return response()->json(['message' => 'Unauthorized'], Status::HTTP_FORBIDDEN);
        }

        $vote = GameProposalVote::query()->firstOrNew([
            'user_id' => $user->id,
            'game_proposal_id' => $proposal->id,
        ]);

        $vote->vote_type = 'down';
        $vote->value = -1;
        $vote->save();

        return response()->json([
            "message" => "Proposal has been disliked",
        ], Status::HTTP_OK);
    }

    public function removeReaction(GameProposal $proposal): JsonResponse
    {
        $user = auth()->user();

        $vote = GameProposalVote::query()->where([
            'user_id' => $user->id,
            'game_proposal_id' => $proposal->id,
        ])->first();

        if ($vote) {
            $vote->delete();
        }

        return response()->json([
            "message" => "Reaction has been removed"
        ], Status::HTTP_OK);
    }
}
