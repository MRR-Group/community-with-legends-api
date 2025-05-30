<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->ipAddress("last_login_ip")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->removeColumn("last_login_ip");
        });
    }
};
