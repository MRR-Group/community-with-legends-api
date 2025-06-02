<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Models\Comment;
use CommunityWithLegends\Models\Post;
use CommunityWithLegends\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mchev\Banhammer\Models\Ban;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Response as Status;

class StatisticsController extends Controller
{
    public function index(): JsonResponse
    {
        $postsCount = Post::query()->count();
        $commentsCount = Comment::query()->count();
        $usersCount = User::query()->count();

        $userGrowthAllTime = User::query()->select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as count"))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy("date")
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->count]);

        $postsGrowthAllTime = Post::query()->select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as count"))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy("date")
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->count]);

        $commentsGrowthAllTime = Cache::remember("comments_growth_all_time", 3600, fn() => Comment::select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as count"))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy("date")
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->count]));

        $diskUsage = $this->getDiskUsagePercentage();
        $bannedUsersCount = Ban::query()->count();
        $logs = Activity::query()->count();

        return response()->json([
            "posts" => $postsCount,
            "comments" => $commentsCount,
            "users" => $usersCount,
            "user_growth_all_time" => $userGrowthAllTime,
            "posts_growth_all_time" => $postsGrowthAllTime,
            "comments_growth_all_time" => $commentsGrowthAllTime,
            "disk_usage_percentage" => $diskUsage,
            "banned_users" => $bannedUsersCount,
            "logs" => $logs,
        ], Status::HTTP_OK);
    }

    private function getDiskUsagePercentage(): float
    {
        $totalSpace = disk_total_space(public_path());
        $freeSpace = disk_free_space(public_path());

        if ($totalSpace === 0) {
            return 0;
        }

        return round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
    }
}
