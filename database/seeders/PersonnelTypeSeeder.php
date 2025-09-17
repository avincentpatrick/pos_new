<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PersonnelType;

class PersonnelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PersonnelType::create(['personnel_type_name' => 'Driver']);
        PersonnelType::create(['personnel_type_name' => 'Delivery Helper']);
    }
}
