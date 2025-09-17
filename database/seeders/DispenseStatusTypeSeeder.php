<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DispenseStatusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('dispense_status_types')->insert([
            ['dispense_status_type_name' => 'Returned'], // ID 1
            ['dispense_status_type_name' => 'Ongoing Delivery'], // ID 2
            ['dispense_status_type_name' => 'Completed'], // ID 3
        ]);
    }
}
