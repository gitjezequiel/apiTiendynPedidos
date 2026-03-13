<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop FK only if it still exists (idempotent)
        $fks = \DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'order_items'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME = 'order_items_ibfk_2'
        ");
        if (count($fks) > 0) {
            \DB::statement('ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2');
        }

        // Make nullable if not already
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_item_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Restore the original non-nullable column and FK
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_item_id')->nullable(false)->change();
        });
        \DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_ibfk_2 FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)');
    }
};
