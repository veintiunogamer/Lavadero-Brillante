<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->string('license_plaque', 20)->nullable()->change();
            $table->string('temp_registration', 20)->nullable()->after('license_plaque');
        });
    }

    public function down(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropColumn('temp_registration');
            $table->string('license_plaque', 10)->nullable(false)->change();
        });
    }
};
