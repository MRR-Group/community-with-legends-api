<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use Carbon\Carbon;
use CommunityWithLegends\Http\Requests\CreatePostRequest;
use CommunityWithLegends\Models\Post;
use CommunityWithLegends\Models\PostAsset;
use CommunityWithLegends\Models\Reaction;
use Illuminate\Http\JsonResponse;
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
            "message" => "Post has been created",
        ], Status::HTTP_CREATED);
    }

    public function index(): JsonResponse
    {
        $posts = Post::query()
            ->with(["user", "tags", "game"])
            ->withCount("reactions")
            ->addSelect(["user_reacted" => Reaction::query()->selectRaw("count(*)")
                ->whereColumn("post_id", "posts.id")
                ->where("user_id", auth()->id())
                ->limit(1),
            ])
            ->orderBy("created_at", "desc")
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) {
            $post->user_reacted = $post->user_reacted === 1;

            return $post;
        });

        return response()->json($posts);
    }

    public function getTrendingPosts(): JsonResponse
    {
        $posts = Post::query()
            ->with(["user", "tags", "game"])
            ->withCount("reactions")
            ->addSelect(["user_reacted" => Reaction::query()->selectRaw("count(*)")
                ->whereColumn("post_id", "posts.id")
                ->where("user_id", auth()->id())
                ->limit(1),
            ])
            ->where('created_at', '>', Carbon::now()->subDays(7))
            ->orderBy("user_reacted", "desc")
            ->orderBy("created_at", "desc")
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) {
            $post->user_reacted = $post->user_reacted === 1;

            return $post;
        });

        return response()->json($posts);
    }

    public function getFilteredPosts(): JsonResponse
    {
        $query = Post::query();
        $tagId = request('tag');
        $gameId = request('game');

        if ($tagId && $tagId != 'null') {
            $query->whereHas('tags', function ($query) use ($tagId) {
                $query->where('tags.id', $tagId);
            });
        }

        if ($gameId && $gameId != 'null') {
            $query->where('game_id', $gameId);
        }

        $posts = $query
            ->with(["user", "tags", "game"])
            ->withCount("reactions")
            ->addSelect(["user_reacted" => Reaction::query()->selectRaw("count(*)")
                ->whereColumn("post_id", "posts.id")
                ->where("user_id", auth()->id())
                ->limit(1),
            ])
            ->orderBy("created_at", "desc")
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) {
            $post->user_reacted = $post->user_reacted === 1;

            return $post;
        });

        return response()->json($posts);
    }

    public function show(int $postId): JsonResponse
    {
        $post = Post::query()
            ->with(["user", "tags", "game", 'comments.user'])
            ->withCount("reactions")
            ->addSelect(["user_reacted" => Reaction::query()->selectRaw("count(*)")
                ->whereColumn("post_id", "posts.id")
                ->where("user_id", auth()->id())
                ->limit(1),
            ])
            ->where('id', $postId)
            ->first();

        $post->user_reacted = $post->user_reacted === 1;

        return response()->json($post);
    }

    public function addReaction(int $postId): JsonResponse
    {
        $post = Post::query()->findOrFail($postId);

        $reaction = $post->reactions()
            ->where("user_id", auth()->id())
            ->first();

        if ($reaction) {
            return response()->json([
                "message" => "You have already reacted to this post",
            ], Status::HTTP_CONFLICT);
        }

        $post->reactions()->create([
            "user_id" => auth()->id(),
        ]);

        return response()->json([
            "message" => "Reaction added successfully",
        ], Status::HTTP_CREATED);
    }

    public function removeReaction(int $postId): JsonResponse
    {
        $post = Post::query()->findOrFail($postId);

        $reaction = $post->reactions()
            ->where("user_id", auth()->id())
            ->first();

        if (!$reaction) {
            return response()->json([
                "message" => "No reaction to remove",
            ], Status::HTTP_NOT_FOUND);
        }

        $reaction->delete();

        return response()->json([
            "message" => "Reaction removed successfully",
        ], Status::HTTP_OK);
    }
}
