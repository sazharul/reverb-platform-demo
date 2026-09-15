<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['users.create', 'roles.assign', 'subscriptions.assign'] as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']); // Ensure the admin role exists

        $admin->givePermissionTo(['users.create', 'roles.assign', 'subscriptions.assign']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::findByName('admin');
        if ($admin) {
            $admin->revokePermissionTo(['users.create', 'roles.assign', 'subscriptions.assign']);
        }

        Permission::where('name', 'users.create')->delete();
        Permission::where('name', 'roles.assign')->delete();
        Permission::where('name', 'subscriptions.assign')->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
