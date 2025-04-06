<?php

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

        $postData->user_id = auth()->id();

        $post = new Post($validated);
        $post->save();

        return response()->json([
            'message' => 'Post has been created'
        ], Status::HTTP_OK);
    }
}
