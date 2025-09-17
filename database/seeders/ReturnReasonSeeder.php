<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReturnReason;

class ReturnReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReturnReason::create(['return_reason_name' => 'Overestimate Order']);
        ReturnReason::create(['return_reason_name' => 'Damaged Packaging']);
        ReturnReason::create(['return_reason_name' => 'Wrong Packaging']);
        ReturnReason::create(['return_reason_name' => 'Underweight Product']);
        ReturnReason::create(['return_reason_name' => 'Others']);
    }
}
