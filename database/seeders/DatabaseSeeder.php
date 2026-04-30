<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin Owner',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $categories = [
            'Sand', 'Cement', 'Rod', 'Labour', 'Transport', 'Electrical', 'Plumbing', 'Bricks', 'Others'
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::create(['name' => $cat]);
        }
    }
}
