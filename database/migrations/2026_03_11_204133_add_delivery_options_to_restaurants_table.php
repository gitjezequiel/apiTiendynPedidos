<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->enum('service_type', ['local', 'delivery', 'both'])->default('both')->after('is_open');
            $table->json('delivery_zones')->nullable()->after('service_type');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'delivery_zones']);
        });
    }
};
