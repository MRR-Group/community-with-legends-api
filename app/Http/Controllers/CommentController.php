<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\CreateCommentRequest;
use CommunityWithLegends\Models\Comment;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as Status;

class CommentController extends Controller
{
    public function store(CreateCommentRequest $request, int $postId): JsonResponse
    {
        $validated = $request->validated();

        $comment = new Comment([
            "post_id" => $postId,
            "user_id" => auth()->id(),
            "content" => $validated["content"],
        ]);
        $comment->save();

        return response()->json([
            "message" => "Comment has been created",
        ], Status::HTTP_CREATED);
    }
}
