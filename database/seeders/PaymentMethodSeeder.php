<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            ['payment_method_name' => 'Cash'],
            ['payment_method_name' => 'GCash'],
            ['payment_method_name' => 'Credit'],
            ['payment_method_name' => 'Card Payment'],
            ['payment_method_name' => 'Paymaya'],
            ['payment_method_name' => 'Check'],
            ['payment_method_name' => 'Cash on Delivery'],
        ]);
    }
}
