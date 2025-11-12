<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('vehicle_type', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->dateTime('creation_date');
        });
    }
    public function down() {
        Schema::dropIfExists('vehicle_type');
    }
};
