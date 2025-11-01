<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class VerifyEmailTransactionSchema extends Command
{
    protected $signature = 'email:verify-schema';
    protected $description = 'Verify that all required database tables and columns exist for email transactions';

    public function handle(): int
    {
        $this->info('Verifying email transaction database schema...');

        $errors = [];

        // Check email_configurations table
        if (!Schema::hasTable('email_configurations')) {
            $errors[] = 'Missing table: email_configurations';
        } else {
            $requiredColumns = [
                'id', 'name', 'email', 'password', 'imap_host', 'imap_port', 
                'imap_encryption', 'is_active', 'last_sync_at', 'bank_patterns',
                'created_at', 'updated_at'
            ];

            foreach ($requiredColumns as $column) {
                if (!Schema::hasColumn('email_configurations', $column)) {
                    $errors[] = "Missing column: email_configurations.{$column}";
                }
            }
        }

        // Check email_transactions table
        if (!Schema::hasTable('email_transactions')) {
            $errors[] = 'Missing table: email_transactions';
        } else {
            $requiredColumns = [
                'id', 'email_configuration_id', 'message_id', 'subject', 'sender',
                'transaction_type', 'amount', 'transaction_date', 'description',
                'raw_content', 'processing_status', 'income_id', 'expense_id',
                'error_message', 'created_at', 'updated_at'
            ];

            foreach ($requiredColumns as $column) {
                if (!Schema::hasColumn('email_transactions', $column)) {
                    $errors[] = "Missing column: email_transactions.{$column}";
                }
            }
        }

        // Check if incomes table has email-related columns
        if (Schema::hasTable('incomes')) {
            $emailColumns = ['source', 'email_transaction_id'];
            foreach ($emailColumns as $column) {
                if (!Schema::hasColumn('incomes', $column)) {
                    $this->warn("Optional column missing: incomes.{$column} (may need migration)");
                }
            }
        }

        // Check if expenses table has email-related columns
        if (Schema::hasTable('expenses')) {
            $emailColumns = ['source', 'email_transaction_id'];
            foreach ($emailColumns as $column) {
                if (!Schema::hasColumn('expenses', $column)) {
                    $this->warn("Optional column missing: expenses.{$column} (may need migration)");
                }
            }
        }

        if (empty($errors)) {
            $this->info('✓ All required database tables and columns exist');
            
            // Test basic database operations
            try {
                DB::table('email_configurations')->count();
                DB::table('email_transactions')->count();
                $this->info('✓ Database connectivity and basic operations working');
            } catch (\Exception $e) {
                $this->error('Database operation failed: ' . $e->getMessage());
                return 1;
            }
            
            return 0;
        } else {
            $this->error('Schema verification failed:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
            
            $this->info('');
            $this->info('To fix these issues, run the migrations:');
            $this->info('  php artisan migrate');
            
            return 1;
        }
    }
}