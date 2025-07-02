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
        Schema::create('pending_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('image_path')->nullable();
            $table->string('task_name');
            $table->string('client_name');
            $table->text('description');
            $table->date('due_date');
            $table->enum('status', ['Pending', 'Completed', 'In Progress'])->default('Pending');
            $table->decimal('payment', 10, 2)->default(0.00);
            $table->enum('payment_status', ['Paid', 'Unpaid'])->default('Unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_tasks');
    }
};
