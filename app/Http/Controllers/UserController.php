<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Mostrar todos los usuarios con sus roles.
     */
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    /**
     * Crear un nuevo usuario con un rol asignado.
     */
    public function store(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:10|max:100',
            'role' => 'required|string|exists:roles,name',  // Validar que el rol exista en la tabla roles
            'email' => 'required|string|email|min:10|max:50|unique:users',
            'password' => 'required|string|min:10|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Asignar el rol usando Spatie
        $user->assignRole($request->role);

        return response()->json(['message' => 'Usuario creado exitosamente', 'user' => $user], 201);
    }

    /**
     * Obtener usuarios por diferentes criterios (nombre, correo, ID, etc.).
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['message' => 'Debe proporcionar un término de búsqueda'], 400);
        }

        $users = User::where('name', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->orWhere('id', 'like', '%' . $query . '%')
            ->with('roles')
            ->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No se encontraron usuarios'], 404);
        }

        return response()->json($users);
    }

    /**
     * Actualizar un usuario y su rol.
     */
    public function update(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    // Obtener los roles válidos desde la base de datos usando Spatie
    $validRoles = Role::pluck('name')->toArray(); // Obtiene todos los nombres de los roles

    // Validación de los datos
    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|min:10|max:100',
        'role' => 'sometimes|required|string|in:' . implode(',', $validRoles),  // Validar contra los roles en la base de datos
        'email' => 'sometimes|required|string|email|min:10|max:50|unique:users,email,' . $user->id,
        'password' => 'sometimes|required|string|min:10|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Actualizar el usuario
    $user->update([
        'name' => $request->get('name', $user->name),
        'email' => $request->get('email', $user->email),
        'password' => $request->has('password') ? bcrypt($request->get('password')) : $user->password, // Si la contraseña es proporcionada, se encripta
    ]);

    // Actualizar el rol
    if ($request->has('role')) {
        // Si quieres asignar un único rol
        $user->syncRoles($request->role); // Sincroniza el rol del usuario

        // Si quieres asignar varios roles
        // $user->syncRoles([$request->role1, $request->role2]); 
    }

    return response()->json(['message' => 'Usuario actualizado exitosamente', 'user' => $user]);
}

    /**
     * Eliminar un usuario.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }

    /**
     * Asignar roles y permisos a un usuario.
     */
    public function asignarRolesYPermisos(Request $request, $userId)
    {
        $request->validate([
            'rol' => 'required|string|exists:roles,name',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permissions,name',
        ]);

        $user = User::findOrFail($userId);

        // Asignar el rol al usuario
        $user->syncRoles($request->rol);

        // Asignar los permisos si existen
        if ($request->has('permisos')) {
            $user->syncPermissions($request->permisos);
        }

        return response()->json([
            'message' => 'Roles y permisos asignados correctamente.',
            'user' => $user,
        ]);
    }
}
