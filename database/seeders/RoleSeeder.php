<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions (add more as needed)
        $permissions = [
            'view_prospects',
            'create_prospects',
            'edit_prospects',
            'delete_prospects',
            'view_orders',
            'create_orders',
            'edit_orders',
            'delete_orders',
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'view_reports',
            'manage_users',
            'manage_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $accountManager = Role::firstOrCreate(['name' => 'Account Manager']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $employee = Role::firstOrCreate(['name' => 'employee']);

        // Assign permissions to roles
        $superAdmin->givePermissionTo(Permission::all());

        $accountManager->givePermissionTo([
            'view_prospects',
            'create_prospects',
            'edit_prospects',
            'view_orders',
            'create_orders',
            'edit_orders',
            'view_products',
            'view_reports',
        ]);

        $admin->givePermissionTo([
            'view_prospects',
            'create_prospects',
            'edit_prospects',
            'delete_prospects',
            'view_orders',
            'create_orders',
            'edit_orders',
            'view_products',
            'create_products',
            'edit_products',
            'view_reports',
        ]);

        $employee->givePermissionTo([
            'view_prospects',
            'view_orders',
            'view_products',
        ]);

        // Create default users
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@wofins.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $superAdminUser->assignRole('super_admin');

        $accountManagerUser = User::firstOrCreate(
            ['email' => 'rama@wofins.com'],
            [
                'name' => 'Rama Dhona Utama',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $accountManagerUser->assignRole('Account Manager');

        $adminUser = User::firstOrCreate(
            ['email' => 'qoyyum@wofins.com'],
            [
                'name' => 'Qoyyum',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('admin');

        // Create additional Account Managers
        $accountManager2 = User::firstOrCreate(
            ['email' => 'adel@maknaonline.com'],
            [
                'name' => 'Adel',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $accountManager2->assignRole('Account Manager');

        $accountManager3 = User::firstOrCreate(
            ['email' => 'rina@wofins.com'],
            [
                'name' => 'Rina Mardiana',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $accountManager3->assignRole('Account Manager');

        $this->command->info('Roles, permissions, and users created successfully!');
        $this->command->info('Default users created:');
        $this->command->info('- Super Admin: superadmin@wofins.com / password123');
        $this->command->info('- Rama Dhona Utama: rama@wofins.com / password123');
        $this->command->info('- Qoyyum: qoyyum@wofins.com / password123');
        $this->command->info('- Adel: adel@maknaonline.com / password123');
        $this->command->info('- Rina Mardiana: rina@wofins.com / password123');
    }
}
