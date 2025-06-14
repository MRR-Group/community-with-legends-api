<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Events\CommentAdded;
use CommunityWithLegends\Http\Requests\CreateCommentRequest;
use CommunityWithLegends\Http\Resources\CommentResource;
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

        activity()
            ->causedBy(auth()->user())
            ->performedOn($comment)
            ->log("Created a new comment on post ID: " . $post->id);

        event(new CommentAdded($comment, $comment->post->user));

        return response()->json([
            "message" => __("comment.created"),
            "id" => $comment->id,
        ], Status::HTTP_CREATED);
    }

    public function show(Comment $comment): JsonResponse
    {
        return CommentResource::make($comment)->response();
    }
}
