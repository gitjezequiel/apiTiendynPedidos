<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar a INT (con signo) para coincidir exactamente con restaurants.id (INT signed)
        DB::statement('ALTER TABLE users MODIFY COLUMN restaurant_id INT NULL');

        // Agregar la FK
        Schema::table('users', function (Blueprint $table) {
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
