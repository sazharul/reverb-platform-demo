<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]
            ->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.ban',

            'apps.view',
            'apps.edit',
            'apps.delete',
            'apps.create',

            'events.view',
            'events.retry',
            'events.delete',

            'channels.view',
            'channels.edit',

            'plans.view',
            'plans.create',
            'plans.edit',
            'plans.delete',

            'subscriptions.view',
            'subscriptions.assign',

            'roles.manage',
            'roles.assign',

            'system.settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'users.ban',
            'roles.assign',
            'apps.view', 'apps.edit', 'apps.create',
            'events.view', 'events.retry',
            'channels.view', 'channels.edit',
            'subscriptions.view', 'subscriptions.assign',
        ]);

        $support = Role::firstOrCreate(['name' => 'support']);
        $support->syncPermissions([
            'users.view',
            'apps.view',
            'events.view',
            'channels.view',
        ]);

        $billing = Role::firstOrCreate(['name' => 'billing']);
        $billing->syncPermissions([
            'users.view',
            'apps.view',
        ]);
    }
}
