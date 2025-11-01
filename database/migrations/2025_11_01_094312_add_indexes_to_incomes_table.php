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
        Schema::table('incomes', function (Blueprint $table) {
            // Add index on date column for efficient date-based queries
            $table->index('date', 'incomes_date_index');
            
            // Add composite index on client_id and date for efficient filtering
            $table->index(['client_id', 'date'], 'incomes_client_date_index');
            
            // Add index on date with year and month extraction for monthly queries
            $table->index(\DB::raw('YEAR(date), MONTH(date)'), 'incomes_year_month_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            // Drop the indexes
            $table->dropIndex('incomes_date_index');
            $table->dropIndex('incomes_client_date_index');
            $table->dropIndex('incomes_year_month_index');
        });
    }
};
