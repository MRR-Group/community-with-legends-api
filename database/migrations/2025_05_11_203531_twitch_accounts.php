<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("twitch_accounts", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->string("authorization_code");
            $table->string("access_token");
            $table->string("refresh_token");
            $table->timestamp("token_expires_in");
            $table->timestamps();

            $table->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("twitch_accounts");
    }
};
