<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create or get an item to ensure prices are consistent
        $item = Item::inRandomOrder()->first() ?? Item::factory()->create();

        return [
            'order_id' => Order::factory(),
            'item_id' => $item->id,
            'quantity' => fake()->numberBetween(1, 3),
            'price' => $item->price, // Price at the time of purchase
            'supplier_price' => $item->supplier_price, // Supplier price at the time of purchase
        ];
    }
}