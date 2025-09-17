<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenseTypes = [
            ['expense_type_name' => 'Electricity'],
            ['expense_type_name' => 'Water'],
            ['expense_type_name' => 'Internet Service'],
            ['expense_type_name' => 'Diesel/Gasoline'],
            ['expense_type_name' => 'Packaging Material'],
            ['expense_type_name' => 'Repairs and Maintenance'],
            ['expense_type_name' => 'Employee Salary'],
            ['expense_type_name' => 'Others'],
        ];

        DB::table('expense_types')->insert($expenseTypes);
    }
}
