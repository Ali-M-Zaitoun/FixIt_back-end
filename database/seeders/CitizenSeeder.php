<?php

namespace Database\Seeders;

use App\Models\Citizen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CitizenSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ar_SA');

        $syrianSurnames = ['العلواني', 'الحمصي', 'الدمشقي', 'الراعي', 'الخطيب', 'منصور', 'عبود', 'النجار', 'حداد', 'سليمان', 'إدريس', 'قاسم'];
        $syrianFirstNames = ['أحمد', 'محمد', 'سامر', 'لينا', 'مازن', 'رنا', 'خالد', 'منى', 'ياسر', 'هبة', 'عمر', 'ريم'];
        $syrianPhonePrefixes = ['093', '094', '095', '096', '098', '099'];

        for ($i = 1; $i <= 200; $i++) {
            $firstName = $faker->randomElement($syrianFirstNames);
            $lastName = $faker->randomElement($syrianSurnames);

            $phone = $faker->randomElement($syrianPhonePrefixes) . $faker->numerify('#######');

            $user = User::create([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => "citizen" . $i . "@example.com",
                'phone'      => $phone,
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
