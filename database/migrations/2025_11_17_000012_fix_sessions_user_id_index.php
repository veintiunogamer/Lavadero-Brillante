<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            // Drop existing index if present, then change column type
            try {
                DB::statement('ALTER TABLE `sessions` DROP INDEX `sessions_user_id_index`');
            } catch (\Throwable $e) {
                // ignore if index does not exist or cannot be dropped
            }

            Schema::table('sessions', function (Blueprint $table) {
                $table->string('user_id')->nullable()->index()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            try {
                DB::statement('ALTER TABLE `sessions` DROP INDEX `sessions_user_id_index`');
            } catch (\Throwable $e) {
            }

            Schema::table('sessions', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->change();
            });
        }
    }
};
