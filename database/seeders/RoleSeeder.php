<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        // Role khusus Agent dengan 1 permission 'transaksi mandiri'
        $agentRole = Role::firstOrCreate(['name' => 'Agent']);
        $agentPermission = Permission::firstOrCreate(['name' => 'transaksi mandiri']);
        $agentRole->syncPermissions([$agentPermission]);
    }
}
