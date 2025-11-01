<?php

namespace Database\Factories;

use App\Models\Income;
use App\Models\EmailTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncomeFactory extends Factory
{
    protected $model = Income::class;

    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat(2, 1000, 100000);
        $receivedAmount = $this->faker->randomFloat(2, 0, $totalAmount);
        $pendingAmount = $totalAmount - $receivedAmount;

        return [
            'client_id' => null, // Email transactions don't have clients
            'total_amount' => $totalAmount,
            'pending_amount' => $pendingAmount,
            'received_amount' => $receivedAmount,
            'date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'source' => $this->faker->randomElement(['manual', 'email']),
            'description' => $this->faker->randomElement([
                'Salary payment',
                'Freelance project payment',
                'Investment returns',
                'Rental income',
                'Business revenue',
                'Refund received',
                'Interest payment',
                'Dividend payment'
            ]),
            'email_transaction_id' => null,
        ];
    }

    /**
     * Indicate that the income is from email transaction.
     */
    public function fromEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'email',
            'email_transaction_id' => EmailTransaction::factory(),
            'client_id' => null,
        ]);
    }

    /**
     * Indicate that the income is manually entered.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'manual',
            'email_transaction_id' => null,
        ]);
    }

    /**
     * Indicate that the income is fully received.
     */
    public function fullyReceived(): static
    {
        return $this->state(function (array $attributes) {
            $totalAmount = $attributes['total_amount'];
            return [
                'received_amount' => $totalAmount,
                'pending_amount' => 0.00,
            ];
        });
    }

    /**
     * Indicate that the income is partially received.
     */
    public function partiallyReceived(): static
    {
        return $this->state(function (array $attributes) {
            $totalAmount = $attributes['total_amount'];
            $receivedAmount = $this->faker->randomFloat(2, $totalAmount * 0.1, $totalAmount * 0.8);
            return [
                'received_amount' => $receivedAmount,
                'pending_amount' => $totalAmount - $receivedAmount,
            ];
        });
    }

    /**
     * Indicate that the income is pending (not received yet).
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            $totalAmount = $attributes['total_amount'];
            return [
                'received_amount' => 0.00,
                'pending_amount' => $totalAmount,
            ];
        });
    }

    /**
     * Create income for a specific amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $amount,
            'received_amount' => $amount,
            'pending_amount' => 0.00,
        ]);
    }

    /**
     * Create income for a specific date.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }
}