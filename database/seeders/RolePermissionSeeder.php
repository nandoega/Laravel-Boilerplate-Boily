<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'view users', 'create users', 'edit users', 'delete users',
            'view clients', 'create clients', 'edit clients', 'delete clients',
            'view projects', 'create projects', 'edit projects', 'delete projects',
            'view tasks', 'create tasks', 'edit tasks', 'delete tasks',
            'view teams', 'create teams', 'edit teams', 'delete teams',
            'view invoices', 'create invoices', 'edit invoices', 'delete invoices',
            'view payments', 'create payments', 'edit payments', 'delete payments',
            'view time_entries', 'create time_entries', 'edit time_entries', 'delete time_entries',
            'view reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        // create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'sanctum']);
        $admin      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $manager    = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'sanctum']);
        $user       = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        // Give admin all permissions via gate or direct assign. Direct assign here:
        $admin->givePermissionTo(Permission::all());

        $manager->givePermissionTo([
            'view users', 'view clients', 'create clients', 'edit clients',
            'view projects', 'create projects', 'edit projects',
            'view tasks', 'create tasks', 'edit tasks',
            'view teams', 'create teams', 'edit teams',
            'view time_entries', 'create time_entries', 'edit time_entries',
            'view reports'
        ]);

        $user->givePermissionTo([
            'view projects', 'view tasks', 'edit tasks',
            'view teams', 'view time_entries', 'create time_entries', 'edit time_entries'
        ]);

        // Create initial Super Admin User
        $superAdminUser = clone User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => 'admin123', // Cast to hashed in Model
                'is_active' => true,
            ]
        );
        $superAdminUser->assignRole('super-admin');
    }
}
