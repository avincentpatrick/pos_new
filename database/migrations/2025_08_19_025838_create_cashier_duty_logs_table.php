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
        Schema::create('cashier_duty_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cashier_duty_log_status_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('time_in');
            $table->dateTime('time_out')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_duty_logs');
    }
};
