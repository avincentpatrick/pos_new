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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->integer('storage_duty_log_id')->nullable(); //if 1 returned, if 2 completed
            $table->integer('dispense_status_type_id')->nullable(); //if 1 returned, if 2 completed
            $table->integer('return_reason_id')->nullable(); // reference table return_reasons table. 
            $table->string('return_reason_specify')->nullable(); 
            $table->integer('actual_quantity_dispensed')->nullable(); 
            $table->integer('actual_quantity_returned')->nullable(); 
            $table->text('return_remarks')->nullable(); 
            $table->integer('sales_id');
            $table->integer('product_id');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
