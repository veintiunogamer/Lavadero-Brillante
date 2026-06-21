<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converts discount (decimal) → boolean + discount_value (decimal)
     * Converts taxes   (uuid)    → boolean + taxes_value   (decimal)
     *
     * Migration is safe: existing monetary data in `discount` is preserved in
     * `discount_value`; existing `taxes` uuid is treated as "taxes applied"
     * when not null (taxes_value cannot be recovered – set to 0 for safety).
     */
    public function up(): void
    {
        // Step 1: Add new columns alongside the old ones
        Schema::table('order', function (Blueprint $table) {
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount');
            $table->decimal('taxes_value', 10, 2)->default(0)->after('taxes');
        });

        // Step 2.5: Normalize existing taxes values
        DB::statement("
            UPDATE `order`
            SET `taxes` = 0
            WHERE `taxes` = ''
        ");

        // Step 3: Alter the old columns to boolean
        Schema::table('order', function (Blueprint $table) {
            // Drop the old decimal discount column and re-create as tinyInteger
            $table->tinyInteger('discount')->default(0)->change();
            // Drop the old uuid taxes column and re-create as tinyInteger
            $table->tinyInteger('taxes')->default(0)->change();
        });

        // Step 4: Set boolean value for discount (1 if discount_value > 0)
        DB::statement('
            UPDATE `order`
            SET `discount` = CASE WHEN `discount_value` > 0 THEN 1 ELSE 0 END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * Restores discount_value → discount (decimal), drops discount_value and taxes_value.
     * The original taxes uuid cannot be restored.
     */
    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            // Restore discount as decimal
            $table->decimal('discount', 10, 2)->nullable()->change();
        });

        // Copy discount_value back into discount
        DB::statement('
            UPDATE `order`
            SET `discount` = `discount_value`
        ');

        Schema::table('order', function (Blueprint $table) {
            $table->dropColumn(['discount_value', 'taxes_value']);
            // Restore taxes as nullable string (was uuid stored as string)
            $table->string('taxes')->nullable()->change();
        });

        // Zero out taxes since the original uuid cannot be recovered
        DB::statement('UPDATE `order` SET `taxes` = NULL');
    }
};
