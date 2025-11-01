<?php

namespace Database\Factories;

use App\Models\EmailConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailConfigurationFactory extends Factory
{
    protected $model = EmailConfiguration::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Email Config',
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'test-password-123',
            'imap_host' => $this->faker->randomElement([
                'imap.gmail.com',
                'imap.outlook.com',
                'imap.yahoo.com',
                'imap.sbi.co.in',
                'imap.hdfcbank.com'
            ]),
            'imap_port' => $this->faker->randomElement([993, 143, 995]),
            'imap_encryption' => $this->faker->randomElement(['ssl', 'tls', 'none']),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'last_sync_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 week', 'now'),
            'bank_patterns' => $this->faker->optional(0.5)->randomElements([
                'bank', 'credit', 'debit', 'transaction', 'payment', 'transfer',
                'account', 'statement', 'balance', 'deposit', 'withdrawal'
            ], $this->faker->numberBetween(1, 5)),
        ];
    }

    /**
     * Indicate that the configuration is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the configuration is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the configuration has been synced recently.
     */
    public function recentlySync(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_sync_at' => now()->subMinutes($this->faker->numberBetween(1, 60)),
        ]);
    }

    /**
     * Indicate that the configuration has never been synced.
     */
    public function neverSynced(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_sync_at' => null,
        ]);
    }

    /**
     * Create configuration for specific bank.
     */
    public function forBank(string $bank): static
    {
        $bankConfigs = [
            'sbi' => [
                'name' => 'SBI Email Configuration',
                'imap_host' => 'imap.sbi.co.in',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'bank_patterns' => ['sbi', 'state bank', 'sbi.co.in']
            ],
            'hdfc' => [
                'name' => 'HDFC Bank Email Configuration',
                'imap_host' => 'imap.hdfcbank.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'bank_patterns' => ['hdfc', 'hdfcbank', 'hdfc bank']
            ],
            'icici' => [
                'name' => 'ICICI Bank Email Configuration',
                'imap_host' => 'imap.icicibank.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'bank_patterns' => ['icici', 'icicibank', 'icici bank']
            ],
            'gmail' => [
                'name' => 'Gmail Email Configuration',
                'imap_host' => 'imap.gmail.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'bank_patterns' => ['gmail', 'google']
            ]
        ];

        return $this->state(fn (array $attributes) => 
            $bankConfigs[$bank] ?? $attributes
        );
    }

    /**
     * Create configuration with custom bank patterns.
     */
    public function withBankPatterns(array $patterns): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_patterns' => $patterns,
        ]);
    }

    /**
     * Create configuration that's properly configured and ready to use.
     */
    public function configured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'email' => $this->faker->safeEmail,
            'password' => 'valid-password',
            'imap_host' => 'imap.gmail.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
        ]);
    }

    /**
     * Create configuration that's not properly configured.
     */
    public function unconfigured(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
            'password' => null,
            'imap_host' => null,
        ]);
    }
}