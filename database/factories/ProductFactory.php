<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(),
            'price_in_paise' => $this->faker->numberBetween(50000, 2000000),
            'stock_quantity' => $this->faker->numberBetween(0, 50),
            'is_active' => true,
            'is_featured' => false,
        ];
    }
}
