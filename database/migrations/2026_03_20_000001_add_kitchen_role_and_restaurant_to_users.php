<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ampliar el ENUM de role para incluir 'kitchen'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','customer','superadmin','kitchen') NOT NULL DEFAULT 'customer'");

        // Agregar restaurant_id para ligar usuarios cocina a un restaurante
        // restaurants.id es INT (no bigint), por eso usamos unsignedInteger
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('restaurant_id')->nullable()->after('role');
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','customer','superadmin') NOT NULL DEFAULT 'customer'");
    }
};
