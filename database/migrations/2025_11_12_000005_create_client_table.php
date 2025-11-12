<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('client', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('license_plaque', 10);
            $table->dateTime('creation_date');
        });
    }
    public function down() {
        Schema::dropIfExists('client');
    }
};
