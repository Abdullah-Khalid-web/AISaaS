<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Permission management
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',

            // AI Tools permissions
            'view tools',
            'use tools',
            'create tools',
            'edit tools',
            'delete tools',

            // Subscription management
            'view subscriptions',
            'create subscriptions',
            'edit subscriptions',
            'delete subscriptions',

            // Billing permissions
            'view billing',
            'manage billing',

            // API permissions
            'view api tokens',
            'create api tokens',
            'delete api tokens',

            // Dashboard permissions
            'view dashboard',
            'view analytics',

            // Content management
            'view content',
            'create content',
            'edit content',
            'delete content',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $userRole = Role::create(['name' => 'user']);

        // Assign all permissions to super-admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Assign permissions to admin
        $adminRole->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view roles',
            'view permissions',
            'view tools',
            'use tools',
            'create tools',
            'edit tools',
            'view subscriptions',
            'view billing',
            'view dashboard',
            'view analytics',
            'view content',
            'create content',
            'edit content',
        ]);

        // Assign permissions to manager
        $managerRole->givePermissionTo([
            'view users',
            'view tools',
            'use tools',
            'create tools',
            'edit tools',
            'view subscriptions',
            'view dashboard',
            'view analytics',
            'view content',
            'create content',
            'edit content',
        ]);

        // Assign permissions to regular user
        $userRole->givePermissionTo([
            'view tools',
            'use tools',
            'view dashboard',
            'view billing',
            'view api tokens',
            'create api tokens',
            'delete api tokens',
        ]);

                // Create SUPER ADMIN user (as you requested)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',  // You can change this email
            'password' => Hash::make('admin'),     // Password: admin
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // Create ADMIN user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create REGULAR USER
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('user');

        // // Create 10 more random users
        // User::factory(10)->create()->each(function ($user) {
        //     $user->assignRole('user');
        // });
    }
}
