<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $super_admin = Role::where('name', 'super_admin')->first();
        $ministry_manager = Role::where('name', 'ministry_manager')->first();
        $branch_manager = Role::where('name', 'branch_manager')->first();

        $permissions = Permission::all();
        $super_admin->syncPermissions($permissions);

        $ministry_manager->syncPermissions([
            'ministry.read',
            'ministry.update',
            'branch.create',
            'branch.read',
            'branch.update',
            'employee.read',
        ]);

        $branch_manager->syncPermissions([
            'branch.read',
            'branch.update',
            'employee.read',
        ]);
    }
}
