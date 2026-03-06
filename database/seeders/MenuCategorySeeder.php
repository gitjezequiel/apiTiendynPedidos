<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuCategory;
use App\Models\Restaurant;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = Restaurant::all();

        $categories = [
            'Platos fuertes',
            'Entradas',
            'Bebidas',
            'Postres',
            'Sopas',
            'Especiales del día'
        ];

        foreach ($restaurants as $restaurant) {
            foreach ($categories as $index => $categoryName) {
                // Solo crear si no existe ya para ese restaurante
                MenuCategory::firstOrCreate([
                    'restaurant_id' => $restaurant->id,
                    'name' => $categoryName
                ], [
                    'sort_order' => $index
                ]);
            }
        }
    }
}
