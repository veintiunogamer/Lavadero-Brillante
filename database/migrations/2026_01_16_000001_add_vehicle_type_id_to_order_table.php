<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->uuid('vehicle_type_id')->nullable()->after('hour_out');
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_type')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            $table->dropForeign(['vehicle_type_id']);
            $table->dropColumn('vehicle_type_id');
        });
    }
};
