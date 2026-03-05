<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Efectivo', 'icon' => '💵'],
            ['name' => 'Tarjeta', 'icon' => '💳'],
            ['name' => 'Tigo Money', 'icon' => '📱'],
            ['name' => 'BAC', 'icon' => '📲'],
            ['name' => 'Banco', 'icon' => '🏦'],
            ['name' => 'Transferencia', 'icon' => '💻'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(['name' => $method['name']], $method);
        }
    }
}
