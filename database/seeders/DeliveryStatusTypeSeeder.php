<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeliveryStatusType;

class DeliveryStatusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliveryStatusType::create(['delivery_status_type_name' => 'Ongoing']);
        DeliveryStatusType::create(['delivery_status_type_name' => 'Delivered']);
        DeliveryStatusType::create(['delivery_status_type_name' => 'Returned']);
        DeliveryStatusType::create(['delivery_status_type_name' => 'Cancelled']);
    }
}
