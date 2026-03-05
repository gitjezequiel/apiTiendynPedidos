<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop FK constraints that reference restaurants.id
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropForeign('menu_categories_ibfk_1');
        });

        // Change restaurants.id to bigint unsigned
        Schema::table('restaurants', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        // Change menu_categories.restaurant_id to match
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')->change();
            $table->foreign('restaurant_id')->references('id')->on('restaurants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->increments('id')->change();
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->unsignedInteger('restaurant_id')->change();
            $table->foreign('restaurant_id')->references('id')->on('restaurants');
        });
    }
};
