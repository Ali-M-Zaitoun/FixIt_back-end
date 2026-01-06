<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\MinistryBranch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ar_SA');

        $branches = MinistryBranch::with('ministry')->get();
        $syrianSurnames = ['العلواني', 'الخطيب', 'منصور', 'عبود', 'النجار', 'سليمان', 'إدريس', 'قاسم'];
        $syrianFirstNames = ['أحمد', 'مازن', 'لينا', 'خالد', 'منى', 'ياسر', 'هبة', 'عمر', 'ريم'];
        $syrianPhonePrefixes = ['093', '094', '095', '096', '098', '099'];

        foreach ($branches as $branch) {
            for ($i = 1; $i <= 4; $i++) {
                $firstName = $faker->randomElement($syrianFirstNames);
                $lastName = $faker->randomElement($syrianSurnames);
                $role = ($i === 1) ? 'branch_manager' : 'employee';

                $user = User::create([
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $faker->unique()->userName() . rand(1000, 9999) . "@gov.sy",
                    'phone'      => $faker->randomElement($syrianPhonePrefixes) . $faker->numerify('#######'),
                    'role'       => $role,
                    'password'   => Hash::make('password123'),
                    'status'     => true,
                    'address'    => $faker->address,
                ]);

                if ($role != "employee") {
                    $user->syncRoles(['employee', $role]);
                } else {
                    $user->assignRole($role);
                }

                $user->employee()->create([
                    'ministry_id'        => $branch->ministry_id,
                    'ministry_branch_id' => $branch->id,
                    'start_date'         => now()->subYears(rand(1, 5))->subMonths(rand(1, 12)),
                    'end_date'           => null, // Still active                    
                ]);

                if ($role === 'manager') {
                    $branch->update(['manager_id' => $user->id]);
                }
            }
        }
    }
}
