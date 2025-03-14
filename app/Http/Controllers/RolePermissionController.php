<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    // Listar todos los roles
    public function indexRoles()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    // Crear un rol
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create([
            'name' => $request->name,
        ]);

        // Asignar permisos al rol si existen
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'Rol creado exitosamente', 'role' => $role], 201);
    }

    // Eliminar un rol
    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['message' => 'Rol eliminado exitosamente']);
    }

    // Asignar permisos a un rol
    public function assignPermissionsToRole(Request $request, $roleId)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::findOrFail($roleId);
        $role->syncPermissions($request->permissions);

        return response()->json(['message' => 'Permisos asignados al rol correctamente']);
    }

    // Listar todos los permisos
    public function indexPermissions()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    // Crear un permiso
    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Permiso creado exitosamente', 'permission' => $permission], 201);
    }

    // Eliminar un permiso
    public function destroyPermission($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permiso eliminado exitosamente']);
    }

    // Listar los permisos asignados a un rol
    public function getPermissionsForRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        $permissions = $role->permissions; // Obtiene los permisos asignados al rol

        return response()->json($permissions);
    }
}
