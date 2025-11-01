<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_transactions', function (Blueprint $table) {
            $table->integer('retry_count')->default(0)->after('error_message');
            $table->timestamp('last_retry_at')->nullable()->after('retry_count');
        });
    }

    public function down(): void
    {
        Schema::table('email_transactions', function (Blueprint $table) {
            $table->dropColumn(['retry_count', 'last_retry_at']);
        });
    }
};