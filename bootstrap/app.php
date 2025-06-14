<?php

declare(strict_types=1);

use CommunityWithLegends\Http\Middleware\SetLocaleFromHeader;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        api: __DIR__ . "/../routes/api.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: "*");
        $middleware->statefulApi();

        $middleware->append(SetLocaleFromHeader::class);
        $middleware->append(StartSession::class);

        $middleware->alias([
            "role" => RoleMiddleware::class,
            "permission" => PermissionMiddleware::class,
            "role_or_permission" => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(fn(AuthenticationException $e, $request) => response()->json(["message" => __("auth.unauthorized")], 401));
    })->create();
