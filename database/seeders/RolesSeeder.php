<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'employee',
            'citizen',
            'ministry_manager',
            'branch_manager',
            'former_ministry_manager',
            'former_branch_manager',
        ];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }
}
