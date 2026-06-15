<?php

namespace Database\Factories;

use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentRequest>
 */
class PaymentRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 50, 5000);
        $rate = fake()->randomFloat(6, 0.8, 6.5);

        return [
            'user_id' => User::factory(),
            'amount' => $amount,
            'currency' => fake()->randomElement(config('currencies.supported')),
            'exchange_rate' => $rate,
            'exchange_rate_source' => config('currencies.exchange_rate.base_url'),
            'exchange_rate_fetched_at' => now(),
            'amount_eur' => round($amount / $rate, 2),
            'description' => fake()->sentence(),
            'status' => PaymentRequest::STATUS_PENDING,
            'expires_at' => now()->addHours(PaymentRequest::EXPIRATION_HOURS),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentRequest::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentRequest::STATUS_REJECTED,
            'approved_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentRequest::STATUS_EXPIRED,
            'created_at' => now()->subHours(60),
            'expires_at' => now()->subHours(12),
        ]);
    }
}
