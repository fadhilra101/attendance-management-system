<?php

namespace Database\Seeders;

use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // // Create Roles
        // $adminRole = Role::create(['name' => 'Admin']);
        // $superAdminRole = Role::create(['name' => 'Super Admin']);
        // $hrdRole = Role::create(['name' => 'HRD']);
        // $managerRole = Role::create(['name' => 'Manager']);

        // // Create Permissions
        // $viewUsersPermission = Permission::create(['name' => 'view_users', 'desc' => 'Can view users']);
        // $editUsersPermission = Permission::create(['name' => 'edit_users', 'desc' => 'Can edit users']);
        // $deleteUsersPermission = Permission::create(['name' => 'delete_users', 'desc' => 'Can delete users']);

        // // Attach Permissions to Roles
        // $adminRole->permissions()->attach([
        //     $viewUsersPermission->id,
        //     $editUsersPermission->id,
        //     $deleteUsersPermission->id
        // ]);

        // $superAdminRole->permissions()->attach([
        //     $viewUsersPermission->id,
        //     $editUsersPermission->id,
        //     $deleteUsersPermission->id
        // ]);

        // $hrdRole->permissions()->attach([
        //     $viewUsersPermission->id
        // ]);

        // $managerRole->permissions()->attach([
        //     $viewUsersPermission->id
        // ]);

        // // Create Users
        // User::create([
        //     'name' => 'Super Admin',
        //     'email' => 'superadmin@example.com',
        //     'password' => Hash::make('password123'),
        //     'verified_at' => now(),
        //     'role_id' => $superAdminRole->id
        // ]);

        // User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password123'),
        //     'verified_at' => now(),
        //     'role_id' => $adminRole->id
        // ]);

        // User::create([
        //     'name' => 'HRD User',
        //     'email' => 'hrd@example.com',
        //     'password' => Hash::make('password123'),
        //     'verified_at' => now(),
        //     'role_id' => $hrdRole->id
        // ]);

        // User::create([
        //     'name' => 'Manager User',
        //     'email' => 'manager@example.com',
        //     'password' => Hash::make('password123'),
        //     'verified_at' => now(),
        //     'role_id' => $managerRole->id
        // ]);

        $holidays = [
            [
                'name' => 'New Year',
                'date' => '2025-01-01',
                'type' => 'national',
                'is_recurring' => true,
            ],
            [
                'name' => 'Independence Day',
                'date' => '2025-08-17',
                'type' => 'national',
                'is_recurring' => true,
            ],
            // Add more holidays as needed
            [
                'name' => 'Chinese New Year',
                'date' => '2025-02-01',
                'type' => 'national',
                'is_recurring' => true,
            ],
            [
                'name' => 'Isra Miraj',
                'date' => '2025-02-15',
                'type' => 'national',
                'is_recurring' => false,
            ],
            [
                'name' => 'Nyepi',
                'date' => '2025-03-21',
                'type' => 'national',
                'is_recurring' => false,
            ],
            [
                'name' => 'Good Friday',
                'date' => '2025-04-18',
                'type' => 'national',
                'is_recurring' => false,
            ],
            [
                'name' => 'Eid al-Fitr',
                'date' => '2025-05-02',
                'type' => 'national',
                'is_recurring' => false,
            ],
            [
                'name' => 'Vesak Day',
                'date' => '2025-05-15',
                'type' => 'national',
                'is_recurring' => false,
            ],
            [
                'name' => 'Eid al-Adha',
                'date' => '2025-07-09',
                'type' => 'national',
                'is_recurring' => false,
            ],
            [
                'name' => 'Islamic New Year',
                'date' => '2025-07-30',
                'type' => 'national',
                'is_recurring' => false,
            ],
            [
                'name' => 'Christmas Day',
                'date' => '2025-12-25',
                'type' => 'national',
                'is_recurring' => true,
            ]
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}
