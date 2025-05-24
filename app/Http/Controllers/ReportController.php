<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\ReportRequest;
use CommunityWithLegends\Http\Resources\ReportResource;
use CommunityWithLegends\Models\Comment;
use CommunityWithLegends\Models\Post;
use CommunityWithLegends\Models\Report;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function storePost(ReportRequest $request, Post $post): JsonResponse
    {
        return $this->storeReport($request, $post);
    }

    public function storeComment(ReportRequest $request, Comment $comment): JsonResponse
    {
        return $this->storeReport($request, $comment);
    }

    public function storeUser(ReportRequest $request, User $user): JsonResponse
    {
        return $this->storeReport($request, $user);
    }

    public function indexPosts(): JsonResponse
    {
        return $this->getReportsByType(Post::class);
    }

    public function indexComments(): JsonResponse
    {
        return $this->getReportsByType(Comment::class);
    }

    public function indexUsers(): JsonResponse
    {
        return $this->getReportsByType(User::class);
    }

    public function index(): JsonResponse
    {
        $reports = Report::with(["user", "reportable"])
            ->latest()
            ->paginate(20);

        return ReportResource::collection($reports)->response();
    }

    protected function storeReport(ReportRequest $request, $reportable): JsonResponse
    {
        $report = new Report($request->validated());

        $report->user()->associate($request->user());
        $reportable->reports()->save($report);

        return response()->json(["message" => "The report has been submitted"], 201);
    }

    protected function getReportsByType(string $type): JsonResponse
    {
        $reports = Report::with(["user", "reportable"])
            ->where("reportable_type", $type)
            ->latest()
            ->paginate(20);

        return ReportResource::collection($reports)->response();
    }
}
