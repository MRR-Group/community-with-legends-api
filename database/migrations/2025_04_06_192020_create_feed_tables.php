<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("games", function (Blueprint $table): void {
            $table->id();
            $table->string("name");
        });

        Schema::create("tags", function (Blueprint $table): void {
            $table->id();
            $table->string("name");
        });

        Schema::create("asset_types", function (Blueprint $table): void {
            $table->id();
            $table->string("name");
        });

        Schema::create("posts", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("game_id")->nullable();
            $table->text("content");
            $table->timestamps();

            $table->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");
            $table->foreign("game_id")
                ->references("id")
                ->on("games")
                ->onDelete("set null");
        });

        Schema::create("comments", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("post_id");
            $table->unsignedBigInteger("user_id");
            $table->text("content");
            $table->timestamps();

            $table->foreign("post_id")
                ->references("id")
                ->on("posts")
                ->onDelete("cascade");
            $table->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");
        });

        Schema::create("reactions", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("post_id");
            $table->unsignedBigInteger("user_id");

            $table->foreign("post_id")
                ->references("id")
                ->on("posts")
                ->onDelete("cascade");
            $table->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");
        });

        Schema::create("post_assets", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("post_id");
            $table->unsignedBigInteger("type_id");
            $table->string('link');
            $table->timestamps();

            $table->foreign("post_id")
                ->references("id")
                ->on("posts")
                ->onDelete("cascade");
            $table->foreign("type_id")
                ->references("id")
                ->on("asset_types")
                ->onDelete("cascade");
        });

        Schema::create("post_tag", function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger("post_id");
            $table->integer('tag_id');
            $table->timestamps();


            $table->foreign("post_id")
                ->references("id")
                ->on("posts")
                ->onDelete("cascade");
            $table->foreign("tag_id")
                ->references("id")
                ->on("tags")
                ->onDelete("set null");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("reactions");
        Schema::dropIfExists("post_assets");
        Schema::dropIfExists("post_tag");
        Schema::dropIfExists("comments");
        Schema::dropIfExists("posts");
        Schema::dropIfExists("games");
        Schema::dropIfExists("tags");
        Schema::dropIfExists("asset_types");
    }
};
