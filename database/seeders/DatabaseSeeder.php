<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $this->call(PaymentMethodSeeder::class);
        // $this->call(OrderTypeSeeder::class);
        // $this->call(UserLevelSeeder::class);
        // $this->call(DenominationSeeder::class);
        // $this->call(RouteSeeder::class);
        // $this->call(DispenseStatusTypeSeeder::class); // New seeder
        // $this->call(ReturnReasonSeeder::class);
        // $this->call(PersonnelTypeSeeder::class);
        // $this->call(DeliveryStatusTypeSeeder::class);
        $this->call(ExpenseTypeSeeder::class); // New seeder
    }
}
