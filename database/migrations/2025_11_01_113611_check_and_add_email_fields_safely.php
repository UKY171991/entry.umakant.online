<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First create the email_configurations table
        Schema::create('email_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('password'); // Will be encrypted
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->default('ssl');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->json('bank_patterns')->nullable(); // Store bank-specific patterns
            $table->timestamps();
        });

        // Then create the email_transactions table
        Schema::create('email_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_configuration_id')->constrained()->onDelete('cascade');
            $table->string('message_id')->unique();
            $table->string('subject');
            $table->string('sender');
            $table->enum('transaction_type', ['income', 'expense']);
            $table->decimal('amount', 10, 2);
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->longText('raw_content');
            $table->enum('processing_status', ['pending', 'processed', 'failed'])->default('pending');
            $table->foreignId('income_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('expense_id')->nullable()->constrained()->onDelete('set null');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['processing_status', 'created_at']);
            $table->index(['transaction_date', 'amount']);
        });

        // Check and add fields to incomes table only if they don't exist
        if (!Schema::hasColumn('incomes', 'source')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->enum('source', ['manual', 'email'])->default('manual')->after('date');
            });
        }
        
        if (!Schema::hasColumn('incomes', 'description')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->text('description')->nullable()->after('source');
            });
        }
        
        if (!Schema::hasColumn('incomes', 'email_transaction_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->foreignId('email_transaction_id')->nullable()->constrained('email_transactions')->onDelete('set null')->after('description');
            });
        }

        // Check and add fields to expenses table only if they don't exist
        if (!Schema::hasColumn('expenses', 'source')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->enum('source', ['manual', 'email'])->default('manual')->after('notes');
            });
        }
        
        if (!Schema::hasColumn('expenses', 'email_transaction_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('email_transaction_id')->nullable()->constrained('email_transactions')->onDelete('set null')->after('source');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_transactions');
        Schema::dropIfExists('email_configurations');
        
        if (Schema::hasColumn('incomes', 'email_transaction_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropForeign(['email_transaction_id']);
                $table->dropColumn('email_transaction_id');
            });
        }
        
        if (Schema::hasColumn('incomes', 'description')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
        
        if (Schema::hasColumn('incomes', 'source')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
        
        if (Schema::hasColumn('expenses', 'email_transaction_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropForeign(['email_transaction_id']);
                $table->dropColumn('email_transaction_id');
            });
        }
        
        if (Schema::hasColumn('expenses', 'source')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
};
