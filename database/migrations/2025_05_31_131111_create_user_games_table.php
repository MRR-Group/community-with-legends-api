<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("user_games", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("game_id")->constrained()->cascadeOnDelete();
            $table->enum("status", ["to_play", "playing", "played"]);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("user_games");
    }
};
