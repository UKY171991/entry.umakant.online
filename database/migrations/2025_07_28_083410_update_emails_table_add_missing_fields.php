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
        Schema::table('emails', function (Blueprint $table) {
            // Add client_name field back as it's used in the application
            if (!Schema::hasColumn('emails', 'client_name')) {
                $table->string('client_name')->nullable()->after('client_id');
            }
            
            // Ensure all fields match what's being used on the emails page
            // These fields should already exist from previous migrations, but let's ensure they're there
            
            if (!Schema::hasColumn('emails', 'email_template')) {
                $table->string('email_template')->nullable();
            }
            
            if (!Schema::hasColumn('emails', 'project_name')) {
                $table->string('project_name')->nullable();
            }
            
            if (!Schema::hasColumn('emails', 'estimated_cost')) {
                $table->decimal('estimated_cost', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('emails', 'timeframe')) {
                $table->string('timeframe')->nullable();
            }
            
            if (!Schema::hasColumn('emails', 'notes')) {
                $table->text('notes')->nullable();
            }
            
            if (!Schema::hasColumn('emails', 'phone')) {
                $table->string('phone')->nullable();
            }
            
            if (!Schema::hasColumn('emails', 'last_email_sent_at')) {
                $table->timestamp('last_email_sent_at')->nullable();
            }
            
            if (!Schema::hasColumn('emails', 'last_whatsapp_sent_at')) {
                $table->timestamp('last_whatsapp_sent_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            // Only drop client_name as other fields might be needed by other parts of the application
            if (Schema::hasColumn('emails', 'client_name')) {
                $table->dropColumn('client_name');
            }
        });
    }
};
