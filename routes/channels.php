<?php

declare(strict_types=1);

use CommunityWithLegends\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel("user.{id}", fn(User $user, mixed $id) => true);
Broadcast::channel("admin", fn(User $user) => true);
