<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Laptop',
            'price' => 1200.00,
            'stock' => 10,
        ]);

        Product::create([
            'name' => 'Mouse',
            'price' => 25.00,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Teclado',
            'price' => 50.00,
            'stock' => 30,
        ]);

        Product::create([
            'name' => 'Monitor',
            'price' => 300.00,
            'stock' => 15,
        ]);
    }
}
