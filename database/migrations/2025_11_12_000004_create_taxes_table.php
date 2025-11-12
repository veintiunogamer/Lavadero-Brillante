<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('percent', 10, 3);
            $table->boolean('status');
            $table->dateTime('creation_date');
        });
    }
    public function down() {
        Schema::dropIfExists('taxes');
    }
};
