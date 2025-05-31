<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("game_proposal_votes", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->foreignId("game_proposal_id")->constrained()->onDelete("cascade");
            $table->unique(["user_id", "game_proposal_id"]);
            $table->enum("vote_type", ["up", "down"]);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("game_proposal_votes");
    }
};
