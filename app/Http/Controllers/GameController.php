<?php

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $filter = $request->input('filter');

        if($filter){
            $games = Game::query()->where('name', 'like', '%' . $filter . '%') ->paginate(20);
        }else{
            $games = Game::query()->paginate(20);
        }


        return response()->json($games);
    }
    public function index(Request $request): JsonResponse
    {
        $games = Game::query()->paginate(20);

        return response()->json($games);
    }
}
