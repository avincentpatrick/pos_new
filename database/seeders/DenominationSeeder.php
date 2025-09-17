<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DenominationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $denominations = [
            ['denomination_name' => '1000 pesos', 'value' => 1000.00],
            ['denomination_name' => '500 pesos', 'value' => 500.00],
            ['denomination_name' => '200 pesos', 'value' => 200.00],
            ['denomination_name' => '100 pesos', 'value' => 100.00],
            ['denomination_name' => '50 pesos', 'value' => 50.00],
            ['denomination_name' => '20 pesos', 'value' => 20.00],
            ['denomination_name' => '10 pesos', 'value' => 10.00],
            ['denomination_name' => '5 pesos', 'value' => 5.00],
            ['denomination_name' => '1 peso', 'value' => 1.00]
        ];

        DB::table('denominations')->insert($denominations);
    }
}
