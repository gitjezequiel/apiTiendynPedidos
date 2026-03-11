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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('delivery_mode', ['pickup', 'delivery'])->default('delivery')->after('notes');
            $table->unsignedBigInteger('delivery_zone_id')->nullable()->after('delivery_mode');
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('delivery_zone_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_mode', 'delivery_zone_id', 'delivery_fee']);
        });
    }
};
