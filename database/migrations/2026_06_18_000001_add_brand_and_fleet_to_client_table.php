<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->string('brand', 50)->nullable()->after('license_plaque');
            $table->boolean('fleet')->default(false)->after('brand');
        });
    }

    public function down(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropColumn(['brand', 'fleet']);
        });
    }
};
