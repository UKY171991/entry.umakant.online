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
            $table->timestamp('last_email_sent_at')->nullable()->after('notes');
            $table->timestamp('last_whatsapp_sent_at')->nullable()->after('last_email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn(['last_email_sent_at', 'last_whatsapp_sent_at']);
        });
    }
};
