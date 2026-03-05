<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('restaurant_schedules')) {
            Schema::create('restaurant_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('restaurant_id');
                $table->string('day'); // Lunes, Martes...
                $table->boolean('is_active')->default(true);
                $table->time('opening_time')->nullable();
                $table->time('closing_time')->nullable();
                $table->timestamps();

                $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_schedules');
    }
};
