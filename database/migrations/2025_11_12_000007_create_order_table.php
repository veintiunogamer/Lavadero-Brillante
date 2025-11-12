<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('order', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id')->nullable();
            $table->uuid('service_id');
            $table->integer('quantity')->length(2);
            $table->integer('dirt_level')->length(2);
            $table->dateTime('hour_in');
            $table->dateTime('hour_out');
            $table->string('vehicle_notes', 250);
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->uuid('taxes')->nullable();
            $table->decimal('total', 10, 2);
            $table->string('order_notes', 250);
            $table->string('extra_notes', 250);
            $table->integer('status')->length(2);
            $table->dateTime('creation_date');
        });
    }
    public function down() {
        Schema::dropIfExists('order');
    }
};
