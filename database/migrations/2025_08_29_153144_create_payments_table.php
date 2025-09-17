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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('cashier_duty_log_id');
            $table->integer('client_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('payment_method_id')->nullable();
            $table->decimal('amount_received', 10, 2)->nullable();
            $table->decimal('amount_change', 10, 2)->nullable();
            $table->string('reference_number')->nullable();
            $table->string('check_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
