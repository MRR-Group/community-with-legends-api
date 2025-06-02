<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use Carbon\Carbon;
use CommunityWithLegends\Events\PostLiked;
use CommunityWithLegends\Http\Requests\CreatePostRequest;
use CommunityWithLegends\Http\Resources\PostResource;
use CommunityWithLegends\Models\Comment;
use CommunityWithLegends\Models\Post;
use CommunityWithLegends\Models\PostAsset;
use CommunityWithLegends\Models\Report;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as Status;

class PostController extends Controller
{
    public function store(CreatePostRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $postData = $validated;

        $postData["user_id"] = auth()->id();

        $post = new Post($postData);
        $post->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($post)
            ->log("Created a new post: " . $post->id);

        if (isset($validated["asset_type_id"]) && isset($validated["asset_link"])) {
            PostAsset::create([
                "post_id" => $post->id,
                "type_id" => $validated["asset_type_id"],
                "link" => $validated["asset_link"],
            ]);
        }

        if (isset($validated["tag_ids"])) {
            $post->tags()->attach($validated["tag_ids"]);
        }

        return response()->json([
            "message" => __("post.created"),
            "id" => $post->id,
        ], Status::HTTP_CREATED);
    }

    public function index(Request $request): JsonResponse
    {
        $posts = Post::query()
            ->with(["user", "tags", "game", "comments.user"])
            ->whereHas("user", fn($query) => $query->notBanned())
            ->orderBy("created_at", "desc")
            ->paginate(10);

        return PostResource::collection($posts)->response();
    }

    public function indexByUser(User $user): JsonResponse
    {
        $posts = $user->posts()
            ->with(["user", "tags", "game", "comments.user"])
            ->whereHas("user", fn($query) => $query->notBanned())
            ->orderBy("created_at", "desc")
            ->paginate(10);

        return PostResource::collection($posts)->response();
    }

    public function getTrendingPosts(): JsonResponse
    {
        $posts = Post::query()
            ->with(["user", "tags", "game", "comments.user"])
            ->where("created_at", ">", Carbon::now()->subDays(7))
            ->whereHas("user", fn($query) => $query->notBanned())
            ->orderBy("user_reacted", "desc")
            ->orderBy("created_at", "desc")
            ->paginate(10);

        return PostResource::collection($posts)->response();
    }

    public function getFilteredPosts(): JsonResponse
    {
        $query = Post::query();
        $tagId = request("tag");
        $gameId = request("game");

        if ($tagId && $tagId !== "null") {
            $query->whereHas("tags", function ($query) use ($tagId): void {
                $query->where("tags.id", $tagId);
            });
        }

        if ($gameId && $gameId !== "null") {
            $query->where("game_id", $gameId);
        }

        $posts = $query
            ->with(["user", "tags", "game", "comments.user"])
            ->whereHas("user", fn($query) => $query->notBanned())
            ->orderBy("created_at", "desc")
            ->paginate(10);

        return PostResource::collection($posts)->response();
    }

    public function show(Post $post): JsonResponse
    {
        return PostResource::make($post)->response();
    }

    public function addReaction(Post $post): JsonResponse
    {
        $reaction = $post->reactions()
            ->where("user_id", auth()->id())
            ->first();

        if ($reaction) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($post)
                ->log("Tried to add reaction to post again: " . $post->id);

            return response()->json([
                "message" => __("post.already_reacted"),
            ], Status::HTTP_CONFLICT);
        }

        $post->reactions()->create([
            "user_id" => auth()->id(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($post)
            ->log("Added reaction to post: " . $post->id);

        event(new PostLiked($post, $post->user));

        return response()->json([
            "message" => __("post.reaction_added"),
        ], Status::HTTP_CREATED);
    }

    public function removeReaction(Post $post): JsonResponse
    {
        $reaction = $post->reactions()
            ->where("user_id", auth()->id())
            ->first();

        if (!$reaction) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($post)
                ->log("Tried to remove a reaction that does not exist for post: " . $post->id);

            return response()->json([
                "message" => __("post.no_reaction"),
            ], Status::HTTP_NOT_FOUND);
        }

        $reaction->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($post)
            ->log("Removed reaction from post: " . $post->id);

        return response()->json([
            "message" => __("post.reaction_removed"),
        ], Status::HTTP_OK);
    }

    public function remove(Post $post): JsonResponse
    {
        if ($post->reports->isEmpty()) {
            $post->reports()->save(new Report(["user_id" => auth()->id()]));
        }

        $post->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($post)
            ->log("Deleted post: " . $post->id);

        return response()->json([
            "message" => __("post.removed"),
        ], Status::HTTP_OK);
    }

    public function removeComment(Comment $comment): JsonResponse
    {
        if ($comment->reports->isEmpty()) {
            $comment->reports()->save(new Report(["user_id" => auth()->id()]));
        }

        $comment->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($comment)
            ->log("Deleted comment: " . $comment->id);

        return response()->json([
            "message" => __("post.comment_removed"),
        ], Status::HTTP_OK);
    }

    public function restoreComment(Comment $comment): JsonResponse
    {
        $comment->restore();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($comment)
            ->log("Restored comment: " . $comment->id);

        return response()->json([
            "message" => __("post.comment_restored"),
        ], Status::HTTP_OK);
    }

    public function restorePost(Post $post): JsonResponse
    {
        $post->restore();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($post)
            ->log("Restored post: " . $post->id);

        return response()->json([
            "message" => __("post.restored"),
        ], Status::HTTP_OK);
    }
}
