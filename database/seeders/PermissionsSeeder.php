<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            // Ministries
            'ministry.create',
            'ministry.read',
            'ministry.update',
            'ministry.delete',

            // Ministry Branches
            'branch.create',
            'branch.read',
            'branch.update',
            'branch.delete',

            // Employees
            'employee.create',
            'employee.read',
            'employee.update',
            'employee.delete',

            // Complaints
            'complaint.create',
            'complaint.read',
            'complaint.update',
            'complaint.delete',
            'complaint.review',
            'complaint.resolve',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
