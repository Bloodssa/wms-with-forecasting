<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('1'),
            'google_id' => null,
            'role' =>  'admin'
        ]);

        User::factory()->create([
            'name' => 'Test Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('1'),
            'google_id' => null,
            'role' =>  'staff'
        ]); 

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class
        ]);
    }
}
