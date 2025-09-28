<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->numberBetween(5000, 150000);

        return [
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'image' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => $price,
            'supplier_price' => $price * fake()->randomFloat(2, 0.6, 0.8), // Supplier price is 60-80% of selling price
            'status' => true,
            'stock' => fake()->numberBetween(10, 200),
            'sold' => 0,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
