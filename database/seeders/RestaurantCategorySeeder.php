<?php

namespace Database\Seeders;

use App\Models\RestaurantCategory;
use Illuminate\Database\Seeder;

class RestaurantCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Asados'],
            ['name' => 'Postres'],
            ['name' => 'Comida Rápida'],
            ['name' => 'Pizzas'],
            ['name' => 'Sushi'],
            ['name' => 'Mexicana'],
            ['name' => 'China'],
            ['name' => 'Mariscos'],
            ['name' => 'Vegetariana'],
            ['name' => 'Cafetería'],
        ];

        foreach ($categories as $category) {
            RestaurantCategory::create($category);
        }
    }
}
