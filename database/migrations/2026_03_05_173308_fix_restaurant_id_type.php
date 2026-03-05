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
        // En MySQL, cambiar una columna autoincrementable que es referenciada por FKs es delicado.
        // Pero como fallaron las FKs anteriores, quizás no hay muchas activas.
        
        Schema::table('restaurants', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->increments('id')->change();
        });
    }
};
