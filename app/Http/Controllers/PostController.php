<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\CreatePostRequest;
use CommunityWithLegends\Models\Post;
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

        return response()->json([
            "message" => "Post has been created",
        ], Status::HTTP_CREATED);
    }

    public function index(): JsonResponse
    {
        $posts = Post::query()->with(["user", "tag", "game"])->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($posts);
    }
}
