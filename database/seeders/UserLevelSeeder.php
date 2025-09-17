<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_levels')->insert([
            ['user_level_name' => 'Administrator'],
            ['user_level_name' => 'Cashier'],
            ['user_level_name' => 'Storage Man'],
        ]);
    }
}
