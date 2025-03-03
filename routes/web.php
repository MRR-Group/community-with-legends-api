<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get("/", fn(): JsonResponse => response()->json([
    "message" => "Welcome",
]))->name('dashboard');
