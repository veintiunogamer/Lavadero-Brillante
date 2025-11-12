<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->string('name', 100);
            $table->string('details', 100);
            $table->decimal('value', 10, 2);
            $table->integer('duration')->length(3);
            $table->dateTime('creation_date');
        });
    }
    public function down() {
        Schema::dropIfExists('services');
    }
};
