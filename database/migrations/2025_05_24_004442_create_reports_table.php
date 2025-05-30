<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("reports", function (Blueprint $table): void {
            $table->id();
            $table->morphs("reportable");
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->text("reason")->nullable();
            $table->timestamp("resolved_at")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("reports");
    }
};
