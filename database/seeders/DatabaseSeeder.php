<?php

namespace Database\Seeders;

use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Roles
        $adminRole = Role::create(['name' => 'Admin']);
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $hrdRole = Role::create(['name' => 'HRD']);
        $managerRole = Role::create(['name' => 'Manager']);

        // Create Permissions
        $viewUsersPermission = Permission::create(['name' => 'view_users', 'desc' => 'Can view users']);
        $editUsersPermission = Permission::create(['name' => 'edit_users', 'desc' => 'Can edit users']);
        $deleteUsersPermission = Permission::create(['name' => 'delete_users', 'desc' => 'Can delete users']);

        // Attach Permissions to Roles
        $adminRole->permissions()->attach([
            $viewUsersPermission->id,
            $editUsersPermission->id,
            $deleteUsersPermission->id
        ]);

        $superAdminRole->permissions()->attach([
            $viewUsersPermission->id,
            $editUsersPermission->id,
            $deleteUsersPermission->id
        ]);

        $hrdRole->permissions()->attach([
            $viewUsersPermission->id
        ]);

        $managerRole->permissions()->attach([
            $viewUsersPermission->id
        ]);

        // Create Users
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password123'),
            'verified_at' => now(),
            'role_id' => $superAdminRole->id
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'verified_at' => now(),
            'role_id' => $adminRole->id
        ]);

        User::create([
            'name' => 'HRD User',
            'email' => 'hrd@example.com',
            'password' => Hash::make('password123'),
            'verified_at' => now(),
            'role_id' => $hrdRole->id
        ]);

        User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password123'),
            'verified_at' => now(),
            'role_id' => $managerRole->id
        ]);
    }
}
class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = ['admin', 'superadmin', 'HRD', 'Manager'];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'id' => Str::random(10), // Generate random 10 char key
                'name' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $roles = DB::table('roles')->pluck('id'); // Get all role IDs

        foreach (range(1, 3) as $index) {
            DB::table('users')->insert([
                'id' => $index,
                'name' => 'User ' . $index,
                'email' => 'user' . $index . '@example.com',
                'password' => Hash::make('password123'), // Example hashed password
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'role_id' => $roles->random(), // Assign random role_id
            ]);
        }
    }
}

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $roles = DB::table('roles')->pluck('id'); // Get all role IDs

        foreach (range(1, 5) as $index) {
            DB::table('permissions')->insert([
                'id' => $index,
                'name' => 'Permission ' . $index,
                'desc' => 'Description for permission ' . $index,
                'created_at' => now(),
                'updated_at' => now(),
                'role_id' => $roles->isNotEmpty() ? $roles->random() : null, // Assign random role_id or null
            ]);
        }
    }
}
