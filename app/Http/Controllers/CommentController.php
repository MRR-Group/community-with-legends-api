<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\CreateCommentRequest;
use CommunityWithLegends\Models\Comment;
use CommunityWithLegends\Models\Post;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as Status;

class CommentController extends Controller
{
    public function store(CreateCommentRequest $request, Post $post): JsonResponse
    {
        $validated = $request->validated();

        $comment = new Comment([
            "post_id" => $post->id,
            "user_id" => auth()->id(),
            "content" => $validated["content"],
        ]);
        $comment->save();

        return response()->json([
            "message" => "Comment has been created",
        ], Status::HTTP_CREATED);
    }
}
