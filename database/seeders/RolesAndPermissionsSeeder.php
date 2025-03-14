<?php

namespace Database\Seeders;

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos
        Permission::create(['name' => 'crear usuario']);
        Permission::create(['name' => 'editar usuario']);
        Permission::create(['name' => 'eliminar usuario']);
        Permission::create(['name' => 'ver usuario']);

        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Asignar permisos a los roles
        $adminRole->givePermissionTo(['crear usuario', 'editar usuario', 'eliminar usuario', 'ver usuario']);
        $userRole->givePermissionTo('ver usuario');
    }
}
