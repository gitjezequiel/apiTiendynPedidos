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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('restaurant_id');
            $table->integer('order_id')->nullable();
            $table->tinyInteger('score'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'restaurant_id']); // una reseña por usuario por restaurante
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
