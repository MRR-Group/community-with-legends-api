<?php

declare(strict_types=1);

use CommunityWithLegends\Enums\GameProposalStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("game_proposals", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("target_user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("game_id")->constrained()->cascadeOnDelete();
            $table->string("status")->default(GameProposalStatus::Pending->value);
            $table->timestamps();

            $table->unique(["user_id", "target_user_id", "game_id"], "unique_user_target_game_proposal");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("game_proposals");
    }
};
