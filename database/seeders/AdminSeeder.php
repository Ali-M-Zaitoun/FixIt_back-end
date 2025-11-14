<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@gmail.com',
            'phone' => '1234567890',
            'role' => 'admin',
            'password' => 'password',
            'status' => true,
            'address' => '123 Admin St, Admin City, Admin Country'
        ]);
        $user->assignRole('super_admin');
    }
}
