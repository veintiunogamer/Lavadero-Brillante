<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50);
            $table->integer('type', false, true)->length(2);
            $table->boolean('status');
            $table->dateTime('creation_date');
        });
    }
    public function down() {
        Schema::dropIfExists('roles');
    }
};
