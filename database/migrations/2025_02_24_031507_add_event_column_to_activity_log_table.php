<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventColumnToActivityLogTable extends Migration
{
    public function up(): void
    {
        Schema::connection(config("activitylog.database_connection"))->table(config("activitylog.table_name"), function (Blueprint $table): void {
            $table->string("event")->nullable()->after("subject_type");
        });
    }

    public function down(): void
    {
        Schema::connection(config("activitylog.database_connection"))->table(config("activitylog.table_name"), function (Blueprint $table): void {
            $table->dropColumn("event");
        });
    }
}
