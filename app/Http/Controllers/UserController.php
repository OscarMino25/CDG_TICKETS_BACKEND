<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Mostrar todos los usuarios.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }


    /**
     * Crear un nuevo usuario.
     */
    public function store(Request $request)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:10|max:100',
            'role' => 'required|string|in:admin,user',  // Roles definidos
            'email' => 'required|string|email|min:10|max:50|unique:users',
            'password' => 'required|string|min:10|confirmed', // Confirmación de contraseña
        ]);

        // Si la validación falla, devolver los errores
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Crear el usuario
        $user = User::create([
            'name' => $request->get('name'),
            'role' => $request->get('role'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')), // Encriptar la contraseña
        ]);

        return response()->json(['message' => 'Usuario creado exitosamente', 'user' => $user], 201);
    }


    /**
     * Obtener usuarios por diferentes criterios (nombre, correo, id, etc.)
     */
    public function search(Request $request)
    {
        // Recuperar el término de búsqueda
        $query = $request->input('query');

        // Validar si se proporciona el término de búsqueda
        if (!$query) {
            return response()->json(['message' => 'Debe proporcionar un término de búsqueda'], 400);
        }

        // Buscar en el nombre, correo, o id
        $users = User::where('name', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->orWhere('id', 'like', '%' . $query . '%') // Si necesitas incluir búsqueda por ID
            ->get();

        // Verificar si se encontraron resultados
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No se encontraron usuarios'], 404);
        }

        return response()->json($users);
    }


    /**
     * Actualizar un usuario.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:10|max:100',  // 'sometimes' significa que no es obligatorio actualizarlo
            'role' => 'sometimes|required|string|in:admin,user',
            'email' => 'sometimes|required|string|email|min:10|max:50|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:10|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Actualizar el usuario
        $user->update([
            'name' => $request->get('name', $user->name),
            'role' => $request->get('role', $user->role),
            'email' => $request->get('email', $user->email),
            'password' => $request->has('password') ? bcrypt($request->get('password')) : $user->password, // Si la contraseña es proporcionada, se encripta
        ]);

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

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json(['message' => 'Perfil actualizado con éxito']);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        // Validación de la contraseña
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Verificar si la contraseña actual es correcta
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'La contraseña actual es incorrecta'], 400);
        }

        // Cambiar la contraseña
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Contraseña cambiada con éxito']);
    }
}
