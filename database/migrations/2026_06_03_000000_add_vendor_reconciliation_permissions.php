<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $webPermissions = [
            'vendor-reconciliations-index',
            'vendor-reconciliations-edit',
        ];

        foreach ($webPermissions as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        Permission::query()->firstOrCreate([
            'name' => 'vendor-reconciliations-index',
            'guard_name' => 'vendor',
        ]);

        $superAdmin = Role::query()
            ->where('name', 'Super Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($webPermissions);
        }

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'Merchant')
            ->whereHas('permissions', fn ($query) => $query->whereIn('name', ['statistics-index', 'vendors-index']))
            ->each(function (Role $role) use ($webPermissions): void {
                $role->givePermissionTo($webPermissions);
            });

        Role::query()
            ->where('guard_name', 'vendor')
            ->whereHas('permissions', fn ($query) => $query->where('name', 'vendor-transactions-index'))
            ->each(function (Role $role): void {
                $role->givePermissionTo('vendor-reconciliations-index');
            });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $names = [
            'vendor-reconciliations-index',
            'vendor-reconciliations-edit',
        ];

        Permission::query()
            ->whereIn('name', $names)
            ->whereIn('guard_name', ['web', 'vendor'])
            ->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
