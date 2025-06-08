<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use Carbon\Carbon;
use CommunityWithLegends\Events\ReportCreated;
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

    public function close(Report $report): JsonResponse
    {
        $report->resolved_at = Carbon::now();
        $report->save();

        activity()
            ->performedOn($report)
            ->causedBy(auth()->user())
            ->log("Closed report #{$report->id}");

        return response()->json(["message" => __("report.resolved")], 200);
    }

    public function reopen(Report $report): JsonResponse
    {
        $report->resolved_at = null;
        $report->save();

        activity()
            ->performedOn($report)
            ->causedBy(auth()->user())
            ->log("Reopened report #{$report->id}");

        return response()->json(["message" => __("report.reopened")], 200);
    }

    protected function storeReport(ReportRequest $request, $reportable): JsonResponse
    {
        $report = new Report($request->validated());

        $report->user()->associate($request->user());
        $reportable->reports()->save($report);

        $type = $report->reportable_type;

        activity()
            ->performedOn($reportable)
            ->causedBy($request->user())
            ->withProperties([
                "report_id" => $report->id,
                "reportable_type" => $type,
                "report_reason" => $request->input("reason"),
            ])
            ->log("Submitted a report for $type");

        event(new ReportCreated($report));

        return response()->json(["message" => __("report.submitted")], 201);
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
