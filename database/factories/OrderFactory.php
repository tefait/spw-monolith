<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Decide if the order is from a registered user or a guest
        $isRegisteredUser = fake()->boolean(75); // 75% chance of being a registered user
        $user = $isRegisteredUser ? User::factory()->create(['role' => 'customer']) : null;

        return [
            'transaction_code' => 'TRX-' . now()->timestamp . fake()->unique()->randomNumber(4),
            'customer_name' => $user->name ?? fake()->name(),
            'whatsapp_number' => $user->whatsapp_number ?? fake()->phoneNumber(),
            'email' => $user->email ?? fake()->safeEmail(),
            'user_has_account' => (bool)$user,
            'user_id' => $user->id ?? null,
            'payment_method' => fake()->randomElement(['qris', 'cash']),
            'status' => fake()->randomElement(['paid', 'done']),
            'notes' => fake()->optional()->sentence(),
            'total_amount' => 0, // IMPORTANT: Calculate this in your seeder after adding order items
            'cash_given' => null,
            'change' => null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
