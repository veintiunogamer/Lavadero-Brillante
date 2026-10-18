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
                $table->string('user_id')->nullable()->change();
            });

            // Solo re-crea el índice si no existe (evita error "index already
            // exists" en SQLite, donde el rebuild de tabla lo conserva).
            if (!Schema::hasIndex('sessions', 'sessions_user_id_index')) {
                Schema::table('sessions', function (Blueprint $table) {
                    $table->index('user_id');
                });
            }
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
