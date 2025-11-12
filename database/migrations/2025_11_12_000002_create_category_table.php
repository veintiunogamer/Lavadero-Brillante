<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('category', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cat_name', 100);
            $table->boolean('status');
            $table->dateTime('creation_date');
        });
    }
    public function down() {
        Schema::dropIfExists('category');
    }
};
