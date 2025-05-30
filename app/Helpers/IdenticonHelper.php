<?php

declare(strict_types=1);

namespace CommunityWithLegends\Helpers;

use Identicon\Identicon;
use Illuminate\Support\Facades\Storage;

class IdenticonHelper
{
    private Identicon $identicon;

    public function __construct()
    {
        $this->identicon = new Identicon();
    }

    public function create(string|int $filename, string $data): string
    {
        $image = $this->identicon->getImageData($data, 300);

        return $this->save($filename, $image);
    }

    public static function url(string|int $name): string
    {
        return Storage::disk("avatars")->url($name . ".png");
    }

    private function save(string|int $name, string $imageData): string
    {
        $path = $name . ".png";

        Storage::disk("avatars")->put($path, $imageData);

        return Storage::disk("avatars")->url($path);
    }
}
