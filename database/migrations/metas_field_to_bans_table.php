<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn(config("ban.table"), "metas")) {
            Schema::table(config("ban.table"), function (Blueprint $table): void {
                $table->json("metas")->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn(config("ban.table"), "metas")) {
            Schema::table(config("ban.table"), function (Blueprint $table): void {
                $table->dropColumn("metas");
            });
        }
    }
};
