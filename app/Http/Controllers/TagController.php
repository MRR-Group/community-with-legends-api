<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Resources\TagResource;
use CommunityWithLegends\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $filter = $request->input("search");

        if ($filter) {
            $tags = Tag::query()
                ->whereRaw("LOWER(name) LIKE ?", ["%" . strtolower($filter) . "%"])
                ->get(["id", "name"]);
        } else {
            $tags = Tag::query()->get(["id", "name"]);
        }

        return TagResource::collection($tags)->response();
    }

    public function index(Request $request): JsonResponse
    {
        $tags = Tag::query()->get(["id", "name"]);

        return TagResource::collection($tags)->response();
    }
}
