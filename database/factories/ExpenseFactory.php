<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\EmailTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $categories = [
            'Food & Dining', 'Shopping', 'Transportation', 'Utilities', 
            'Healthcare', 'Entertainment', 'ATM', 'Transfer', 'Others'
        ];

        $expenseNames = [
            'Food & Dining' => ['Restaurant bill', 'Swiggy order', 'Zomato delivery', 'Cafe payment'],
            'Shopping' => ['Amazon purchase', 'Flipkart order', 'Mall shopping', 'Online store payment'],
            'Transportation' => ['Uber ride', 'Ola cab', 'Metro card recharge', 'Fuel payment'],
            'Utilities' => ['Electricity bill', 'Water bill', 'Internet bill', 'Mobile recharge'],
            'Healthcare' => ['Hospital payment', 'Pharmacy bill', 'Doctor consultation', 'Medical test'],
            'Entertainment' => ['Movie ticket', 'Netflix subscription', 'Spotify premium', 'Gaming purchase'],
            'ATM' => ['ATM withdrawal', 'Cash withdrawal'],
            'Transfer' => ['UPI transfer', 'Bank transfer', 'NEFT payment', 'RTGS transfer'],
            'Others' => ['Miscellaneous expense', 'Other payment', 'General expense']
        ];

        $category = $this->faker->randomElement($categories);
        $expenseName = $this->faker->randomElement($expenseNames[$category]);

        return [
            'expense_name' => $expenseName,
            'amount' => $this->faker->randomFloat(2, 10, 10000),
            'category' => $category,
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
            'date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'notes' => $this->faker->optional(0.6)->sentence(),
            'source' => $this->faker->randomElement(['manual', 'email']),
            'email_transaction_id' => null,
        ];
    }

    /**
     * Indicate that the expense is from email transaction.
     */
    public function fromEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'email',
            'email_transaction_id' => EmailTransaction::factory(),
            'status' => 'paid', // Email transactions are already completed
        ]);
    }

    /**
     * Indicate that the expense is manually entered.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'manual',
            'email_transaction_id' => null,
        ]);
    }

    /**
     * Indicate that the expense is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    /**
     * Indicate that the expense is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the expense is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Create expense for a specific category.
     */
    public function category(string $category): static
    {
        $expenseNames = [
            'Food & Dining' => ['Restaurant bill', 'Food delivery', 'Cafe payment'],
            'Shopping' => ['Online purchase', 'Store payment', 'Shopping bill'],
            'Transportation' => ['Taxi ride', 'Public transport', 'Fuel payment'],
            'Utilities' => ['Utility bill', 'Service payment', 'Monthly subscription'],
            'Healthcare' => ['Medical expense', 'Healthcare payment', 'Medicine purchase'],
            'Entertainment' => ['Entertainment expense', 'Subscription payment', 'Leisure activity'],
            'ATM' => ['Cash withdrawal', 'ATM transaction'],
            'Transfer' => ['Money transfer', 'Payment transfer', 'Fund transfer'],
            'Others' => ['Miscellaneous expense', 'Other payment']
        ];

        $expenseName = $this->faker->randomElement($expenseNames[$category] ?? ['General expense']);

        return $this->state(fn (array $attributes) => [
            'category' => $category,
            'expense_name' => $expenseName,
        ]);
    }

    /**
     * Create expense for a specific amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Create expense for a specific date.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Create expense with specific merchant name.
     */
    public function merchant(string $merchantName): static
    {
        return $this->state(fn (array $attributes) => [
            'expense_name' => "Payment to {$merchantName}",
        ]);
    }
}