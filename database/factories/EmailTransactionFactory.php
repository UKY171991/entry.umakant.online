<?php

namespace Database\Factories;

use App\Models\EmailConfiguration;
use App\Models\EmailTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTransaction>
 */
class EmailTransactionFactory extends Factory
{
    protected $model = EmailTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transactionType = $this->faker->randomElement(['income', 'expense']);
        $amount = $this->faker->randomFloat(2, 10, 50000);
        
        return [
            'email_configuration_id' => EmailConfiguration::factory(),
            'message_id' => $this->faker->unique()->uuid,
            'subject' => $this->generateSubject($transactionType, $amount),
            'sender' => $this->faker->randomElement([
                'alerts@sbi.co.in',
                'noreply@hdfc.com', 
                'notifications@icici.com',
                'alerts@axisbank.com'
            ]),
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'transaction_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'description' => $this->generateDescription($transactionType),
            'raw_content' => $this->generateRawContent($transactionType, $amount),
            'processing_status' => $this->faker->randomElement(['pending', 'processed', 'failed']),
            'income_id' => null,
            'expense_id' => null,
            'error_message' => null
        ];
    }

    /**
     * Indicate that the transaction is processed successfully.
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'processing_status' => 'processed',
            'error_message' => null
        ]);
    }

    /**
     * Indicate that the transaction failed processing.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'processing_status' => 'failed',
            'error_message' => $this->faker->sentence()
        ]);
    }

    /**
     * Generate a realistic subject line
     */
    private function generateSubject(string $type, float $amount): string
    {
        if ($type === 'income') {
            return $this->faker->randomElement([
                "Amount Credited - Rs. " . number_format($amount, 2),
                "Salary Credited",
                "Payment Received - Rs. " . number_format($amount, 2),
                "Refund Processed"
            ]);
        } else {
            return $this->faker->randomElement([
                "Account Debited - Rs. " . number_format($amount, 2),
                "Payment Made",
                "UPI Transaction - Rs. " . number_format($amount, 2),
                "ATM Withdrawal"
            ]);
        }
    }

    /**
     * Generate a realistic description
     */
    private function generateDescription(string $type): string
    {
        if ($type === 'income') {
            return $this->faker->randomElement([
                'Salary payment received',
                'Refund from merchant',
                'Interest credited',
                'Payment received from client'
            ]);
        } else {
            return $this->faker->randomElement([
                'Online purchase payment',
                'ATM cash withdrawal',
                'Bill payment processed',
                'UPI transfer to merchant'
            ]);
        }
    }

    /**
     * Generate realistic raw email content
     */
    private function generateRawContent(string $type, float $amount): string
    {
        $accountNumber = 'XXXXXX' . $this->faker->numberBetween(1000, 9999);
        $refNumber = strtoupper($this->faker->bothify('TXN##########'));
        
        if ($type === 'income') {
            return "Dear Customer, Your account {$accountNumber} has been credited with Rs. " . 
                   number_format($amount, 2) . " on " . now()->format('d/m/Y') . 
                   ". Available balance: Rs. " . number_format($this->faker->numberBetween(5000, 100000), 2) . 
                   ". Ref: {$refNumber}";
        } else {
            return "Dear Customer, Your account {$accountNumber} has been debited with Rs. " . 
                   number_format($amount, 2) . " on " . now()->format('d/m/Y') . 
                   " at " . $this->faker->company . ". Available balance: Rs. " . 
                   number_format($this->faker->numberBetween(1000, 50000), 2) . ". Ref: {$refNumber}";
        }
    }
}