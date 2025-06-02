<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Resources\LogResource;
use Illuminate\Http\JsonResponse;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = Activity::query()->orderByDesc("created_at")->paginate(50);

        return LogResource::collection($logs)->response();
    }
}
