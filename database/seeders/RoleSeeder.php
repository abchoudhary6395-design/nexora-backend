<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'is_system' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'is_system' => true],
            ['name' => 'Manager', 'slug' => 'manager', 'is_system' => true],
            ['name' => 'Sales Executive', 'slug' => 'sales-executive', 'is_system' => true],
            ['name' => 'Support Executive', 'slug' => 'support-executive', 'is_system' => true],
            ['name' => 'Accountant', 'slug' => 'accountant', 'is_system' => true],
            ['name' => 'HR', 'slug' => 'hr', 'is_system' => true],
            ['name' => 'Employee', 'slug' => 'employee', 'is_system' => true],
            ['name' => 'Viewer', 'slug' => 'viewer', 'is_system' => true],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        $modules = ['customers', 'leads', 'companies', 'deals', 'tasks', 'projects', 'invoices', 'reports', 'users'];
        $permissions = [];
        foreach ($modules as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $permissions[] = [
                    'module' => $module,
                    'name' => ucfirst($action).' '.ucfirst($module),
                    'slug' => "{$module}.{$action}",
                ];
            }
        }

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Super Admin and Admin get every permission.
        $allPermissionIds = Permission::pluck('id');
        Role::whereIn('slug', ['super-admin', 'admin'])->each(
            fn (Role $role) => $role->permissions()->sync($allPermissionIds)
        );

        // Manager: view/create/update everywhere, no deletes, plus user viewing.
        $managerPerms = Permission::where('slug', 'not like', '%.delete')->pluck('id');
        Role::where('slug', 'manager')->first()?->permissions()->sync($managerPerms);

        // Sales Executive: full CRM access, read-only elsewhere.
        $salesPerms = Permission::whereIn('module', ['leads', 'customers', 'companies', 'deals', 'tasks'])->pluck('id');
        Role::where('slug', 'sales-executive')->first()?->permissions()->sync($salesPerms);

        // Viewer: view-only across every module.
        $viewerPerms = Permission::where('slug', 'like', '%.view')->pluck('id');
        Role::where('slug', 'viewer')->first()?->permissions()->sync($viewerPerms);
    }
}
