<?php

declare(strict_types=1);

use CommunityWithLegends\Enums\Role;
use CommunityWithLegends\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel("user.{id}", fn(User $user, mixed $id) => $user->id === (int)$id);
Broadcast::channel("admin", fn(User $user) => $user->hasAnyRole([Role::SuperAdministrator, Role::Administrator]));
