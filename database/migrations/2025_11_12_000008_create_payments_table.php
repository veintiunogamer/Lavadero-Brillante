<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_id');
            $table->integer('type')->length(2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->integer('status')->length(2);
            $table->dateTime('creation_date');
        });
    }
    public function down() {
        Schema::dropIfExists('payments');
    }
};
