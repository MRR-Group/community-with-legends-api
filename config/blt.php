<?php

declare(strict_types=1);

use Blumilk\BLT\Helpers\ArrayHelper;
use Blumilk\BLT\Helpers\BooleanHelper;
use Blumilk\BLT\Helpers\ClassHelper;
use Blumilk\BLT\Helpers\NullableHelper;
use Spatie\Permission\Models\Role;

return [
    "namespaces" => [
        "default" => "App\\",
        "types" => [
            "user" => "App\Models\User",
            "role" => Role::class,
        ],
    ],
    "helpers" => [
        "array" => ArrayHelper::class,
        "boolean" => BooleanHelper::class,
        "class" => ClassHelper::class,
        "nullable" => NullableHelper::class,
    ],
];
