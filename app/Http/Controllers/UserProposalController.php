<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Enums\GameProposalStatus;
use CommunityWithLegends\Enums\GameProposalVoteType;
use CommunityWithLegends\Enums\UserGameStatus;
use CommunityWithLegends\Events\GameProposalAccepted;
use CommunityWithLegends\Events\GameProposalAdded;
use CommunityWithLegends\Events\GameProposalRejected;
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
        $gameProposals = GameProposal::with(["user", "targetUser", "game", "votes"])
            ->where("target_user_id", $user->id)
            ->whereNot("status", GameProposalStatus::Accepted->value)
            ->get();

        return GameProposalResource::collection($gameProposals)->response();
    }

    public function show(GameProposal $gameProposal): JsonResponse
    {
        return GameProposalResource::make($gameProposal)->response();
    }

    public function store(User $user, Game $game): JsonResponse
    {
        $authUser = auth()->user();

        if ($user->is($authUser)) {
            activity()
                ->causedBy($authUser)
                ->log("Attempted to send a game proposal to themselves.");

            return response()->json([
                "message" => __("game_proposal.self_proposal"),
            ], Status::HTTP_BAD_REQUEST);
        }

        if (GameProposal::hasUserAlreadyProposed($authUser->id, $user->id, $game->id)) {
            activity()
                ->causedBy($authUser)
                ->log("Attempted to propose a game to user_id {$user->id} for game_id {$game->id}, but already proposed.");

            return response()->json([
                "message" => __("game_proposal.already_proposed"),
            ], Status::HTTP_CONFLICT);
        }

        if (GameProposal::userHasGame($user, $game->id)) {
            activity()
                ->causedBy($authUser)
                ->log("Attempted to propose game_id {$game->id} to user_id {$user->id} but the user already has the game.");

            return response()->json([
                "message" => __("game_proposal.already_has_game"),
            ], Status::HTTP_CONFLICT);
        }

        $gameProposal = new GameProposal([
            "user_id" => $authUser->id,
            "target_user_id" => $user->id,
            "game_id" => $game->id,
        ]);

        $gameProposal->save();

        activity()
            ->causedBy($authUser)
            ->performedOn($gameProposal)
            ->log("Sent game proposal to user_id {$user->id} for game_id {$game->id}.");

        event(new GameProposalAdded($gameProposal, $gameProposal->user));

        return response()->json([
            "message" => __("game_proposal.sent"),
        ], Status::HTTP_CREATED);
    }

    public function destroy(GameProposal $gameProposal): JsonResponse
    {
        if ($gameProposal->user->isNot(auth()->user())) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($gameProposal)
                ->log("Attempted to delete game proposal ID {$gameProposal->id}, unauthorized.");

            return response()->json([
                "message" => __("game_proposal.unauthorized"),
            ], Status::HTTP_FORBIDDEN);
        }

        $gameProposal->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($gameProposal)
            ->log("Deleted game proposal ID {$gameProposal->id}.");

        return response()->json([
            "message" => __("game_proposal.deleted"),
        ], Status::HTTP_OK);
    }

    public function accept(GameProposal $gameProposal): JsonResponse
    {
        $user = auth()->user();

        if ($gameProposal->targetUser->isNot($user)) {
            activity()
                ->causedBy($user)
                ->performedOn($gameProposal)
                ->log("Attempted to accept game proposal ID {$gameProposal->id}, unauthorized.");

            return response()->json([
                "message" => __("game_proposal.unauthorized"),
            ], Status::HTTP_FORBIDDEN);
        }

        $gameProposal->status = GameProposalStatus::Accepted;
        $gameProposal->save();

        $userGame = UserGame::query()->create([
            "user_id" => $user->id,
            "game_id" => $gameProposal->game->id,
            "status" => UserGameStatus::ToPlay,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($gameProposal)
            ->log("Accepted game proposal ID {$gameProposal->id}, created UserGame ID {$userGame->id}.");

        event(new GameProposalAccepted($gameProposal, $gameProposal->user));

        return response()->json([
            "message" => __("game_proposal.accepted"),
            "userGameId" => $userGame->id,
        ], Status::HTTP_CREATED);
    }

    public function reject(GameProposal $gameProposal): JsonResponse
    {
        $user = auth()->user();

        if ($gameProposal->targetUser->isNot($user)) {
            activity()
                ->causedBy($user)
                ->performedOn($gameProposal)
                ->log("Attempted to reject game proposal ID {$gameProposal->id}, unauthorized.");

            return response()->json([
                "message" => __("game_proposal.unauthorized"),
            ], Status::HTTP_FORBIDDEN);
        }

        $gameProposal->status = GameProposalStatus::Rejected;
        $gameProposal->save();

        activity()
            ->causedBy($user)
            ->performedOn($gameProposal)
            ->log("Rejected game proposal ID {$gameProposal->id}.");

        event(new GameProposalRejected($gameProposal, $gameProposal->user));

        return response()->json([
            "message" => __("game_proposal.rejected"),
        ], Status::HTTP_OK);
    }

    public function like(GameProposal $gameProposal): JsonResponse
    {
        $user = auth()->user();

        if ($gameProposal->user->is($user) || $gameProposal->targetUser->is($user)) {
            activity()
                ->causedBy($user)
                ->performedOn($gameProposal)
                ->log("Attempted to like game proposal ID {$gameProposal->id}, unauthorized.");

            return response()->json([
                "message" => __("game_proposal.unauthorized"),
            ], Status::HTTP_FORBIDDEN);
        }

        if ($gameProposal->status !== GameProposalStatus::Pending) {
            activity()
                ->causedBy($user)
                ->performedOn($gameProposal)
                ->log("Attempted to vote on game proposal ID {$gameProposal->id}, but it is no longer pending.");

            return response()->json([
                "message" => __("game_proposal.cannot_vote"),
            ], Status::HTTP_FORBIDDEN);
        }

        GameProposalVote::query()->updateOrCreate(
            [
                "user_id" => $user->id,
                "game_proposal_id" => $gameProposal->id,
            ],
            [
                "vote_type" => GameProposalVoteType::Up,
            ],
        );

        activity()
            ->causedBy($user)
            ->performedOn($gameProposal)
            ->log("Liked game proposal ID {$gameProposal->id}.");

        return response()->json([
            "message" => __("game_proposal.liked"),
        ], Status::HTTP_OK);
    }

    public function dislike(GameProposal $gameProposal): JsonResponse
    {
        $user = auth()->user();

        if ($gameProposal->user->is($user) || $gameProposal->targetUser->is($user)) {
            activity()
                ->causedBy($user)
                ->performedOn($gameProposal)
                ->log("Attempted to dislike game proposal ID {$gameProposal->id}, unauthorized.");

            return response()->json([
                "message" => __("game_proposal.unauthorized"),
            ], Status::HTTP_FORBIDDEN);
        }

        if ($gameProposal->status !== GameProposalStatus::Pending) {
            activity()
                ->causedBy($user)
                ->performedOn($gameProposal)
                ->log("Attempted to vote on game proposal ID {$gameProposal->id}, but it is no longer pending.");

            return response()->json([
                "message" => __("game_proposal.cannot_vote"),
            ], Status::HTTP_FORBIDDEN);
        }

        GameProposalVote::query()->updateOrCreate(
            [
                "user_id" => $user->id,
                "game_proposal_id" => $gameProposal->id,
            ],
            [
                "vote_type" => GameProposalVoteType::Down,
            ],
        );

        activity()
            ->causedBy($user)
            ->performedOn($gameProposal)
            ->log("Disliked game proposal ID {$gameProposal->id}.");

        return response()->json([
            "message" => __("game_proposal.disliked"),
        ], Status::HTTP_OK);
    }

    public function removeReaction(GameProposal $gameProposal): JsonResponse
    {
        $user = auth()->user();

        $vote = GameProposalVote::query()->where([
            "user_id" => $user->id,
            "game_proposal_id" => $gameProposal->id,
        ])->first();

        if ($vote) {
            $vote->delete();
        }

        activity()
            ->causedBy($user)
            ->performedOn($vote)
            ->log("Removed reaction from game proposal ID {$gameProposal->id}.");

        return response()->json([
            "message" => __("game_proposal.removed_reaction"),
        ], Status::HTTP_OK);
    }
}
