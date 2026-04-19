<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Services\PermissionService;
use App\Services\RolesService;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Class RolePermissionSeeder.
 *
 * @see https://spatie.be/docs/laravel-permission/v5/basic-usage/multiple-guards
 */
class RolePermissionSeeder extends Seeder
{
    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly RolesService $rolesService
    ) {
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear any previously cached permissions so fresh ones are visible
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create all permissions
        $this->command->info('Creating permissions...');
        $this->permissionService->createPermissions();

        // Force Spatie to re-read permissions from DB after creation
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create predefined roles with their permissions
        $this->command->info('Creating predefined roles...');
        $roles = $this->rolesService->createPredefinedRoles();

        // Assign superadmin role to superadmin user if exists
        $superadmin = User::where('email', 'superadmin@spendium.com')
            ->orWhere('username', 'superadmin')
            ->first();
        if ($superadmin) {
            $this->command->info('Assigning Superadmin role to superadmin user...');
            $superadmin->assignRole($roles['superadmin']);
        }

        // Assign random legacy roles (Admin/Editor/Subscriber) to factory users only.
        // Named family users get Family Head / Family Member assigned later by FamilySeeder.
        $this->command->info('Assigning random legacy roles to factory users...');
        $availableRoles = ['Admin', 'Editor', 'Subscriber'];
        $factoryUsers = User::where('email', 'not like', '%@spendium.com')->get();

        foreach ($factoryUsers as $user) {
            if (!$user->hasRole('Superadmin')) {
                $randomRole = $availableRoles[array_rand($availableRoles)];
                $user->assignRole($randomRole);
            }
        }

        // Final flush so subsequent seeders see everything
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Roles and Permissions created successfully!');
    }
}
