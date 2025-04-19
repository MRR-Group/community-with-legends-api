<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\CreatePostRequest;
use CommunityWithLegends\Models\Post;
use CommunityWithLegends\Models\PostAsset;
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

        if (isset($validated['asset_type_id']) && isset($validated['asset_link'])) {
            PostAsset::create([
                'post_id' => $post->id,
                'type_id' => $validated['asset_type_id'],
                'link' => $validated['asset_link'],
            ]);
        }

        if (isset($validated['tag_ids'])) {
            $post->tags()->attach($validated['tag_ids']);
        }

        return response()->json([
            "message" => "Post has been created",
        ], Status::HTTP_CREATED);
    }

    public function index(): JsonResponse
    {
        $posts = Post::query()
            ->with(["user", "tags", "game"])
            ->withCount('reactions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($posts);
    }

    public function toggleReaction(int $postId): JsonResponse
    {
        $post = Post::query()->findOrFail($postId);

        $reaction = $post->reactions()
            ->where("user_id", auth()->id())
            ->first();

        if ($reaction) {
            $reaction->delete();

            return response()->json([
                "message" => "Reaction removed successfully",
            ], Status::HTTP_OK);
        }

        $post->reactions()->create([
            'user_id' => auth()->id()
        ]);

        return response()->json([
            "message" => "Reaction added successfully",
        ], Status::HTTP_CREATED);
    }
}
