<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(['role' => 'customer']),
            'item_id' => Item::factory(),
            'amount' => $this->faker->numberBetween(1, 5), // 'amount' here likely means quantity
        ];
    }
}