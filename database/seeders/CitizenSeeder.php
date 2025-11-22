<?php

namespace Database\Seeders;

use App\Models\Citizen;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CitizenSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 1; $i <= 10; $i++) {

            $user = User::create([
                'first_name' => $faker->firstName,
                'last_name'  => $faker->lastName,
                'email'      => $faker->unique()->safeEmail,
                'phone'      => $faker->phoneNumber,
                'role'       => 'citizen',
                'password'   => Hash::make('password123'),
                'status'     => true,
                'address'    => $faker->address,
            ]);

            $user->citizen()->create([
                'user_id'     => $user->id,
                'nationality' => 'Syrian',
                'national_id' => $faker->unique()->numerify('###########'),
            ]);
        }
    }
}
