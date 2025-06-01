<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "roles" => $this->getRoleNames(),
            "email" => $this->email,
            "name" => $this->name,
            "avatar" => $this->avatar,
            "permissions" => $this->permissionsNames(),
            "isBanned" => $this->isBanned(),
            "hasPassword" => $this->hasPassword,
            "created_at" => $this->created_at,
        ];
    }
}
