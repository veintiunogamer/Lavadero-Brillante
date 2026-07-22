<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->unique();
            $table->string('business_name', 200);
            $table->string('nif', 50);
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address', 250);
            $table->string('city', 100);
            $table->string('zipcode', 10);
            $table->dateTime('creation_date');

            $table->foreign('order_id')
                ->references('id')
                ->on('order')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
