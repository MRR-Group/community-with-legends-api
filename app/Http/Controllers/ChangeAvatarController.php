<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Controllers;

use CommunityWithLegends\Http\Requests\ChangeAvatarRequest;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ChangeAvatarController extends Controller
{
    public function store(ChangeAvatarRequest $request): void
    {
        $user = $request->user();
        $upload = $request->file("avatar");
        $image = Image::read($upload)->scale(300, 300)->encodeByExtension("png");

        Storage::disk("avatars")->put($user->id . ".png", $image);

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log("Updated avatar");
    }
}
