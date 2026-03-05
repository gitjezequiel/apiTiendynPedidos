<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla maestra de métodos de pago
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Efectivo, Tarjeta, Tigo Money, etc.
                $table->string('icon')->nullable(); // Para el emoji o icono
                $table->timestamps();
            });
        }

        // Tabla intermedia con los restaurantes (pivot)
        if (!Schema::hasTable('restaurant_payment_methods')) {
            Schema::create('restaurant_payment_methods', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('restaurant_id');
                $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_payment_methods');
        Schema::dropIfExists('payment_methods');
    }
};
