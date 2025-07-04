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
            $table->string('email_template')->nullable();
            $table->string('project_name')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->string('timeframe')->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn(['email_template', 'project_name', 'estimated_cost', 'timeframe', 'notes']);
        });
    }
};
