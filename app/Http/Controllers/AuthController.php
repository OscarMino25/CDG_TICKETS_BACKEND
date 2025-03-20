<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:10|max:100',
            'role' => 'required|string|in:admin,user',
            'email' => 'required|string|email|min:10|max:50|unique:users',
            'password' => 'required|string|min:10|confirmed',


        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        User::create([
            'name' => $request->get('name'),
            'role' => $request->get('role'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);
        return response()->json(['message' => 'User created succesfully'], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|min:10|max:50',
            'password' => 'required|string|min:10',


        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $credentials = $request->only(['email', 'password']);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciales invalidas'], 401);
            }
            return response()->json(['token' => $token], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token', $e], 500);
        }
    }

    public function getUser()
{
    $user = Auth::user();

    // Obtener los roles del usuario
    $roles = $user->roles; // Esto debería funcionar si el trait HasRoles está presente

    return response()->json([
        'user' => $user,
        'roles' => $roles
    ], 200);
}

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logued out succesfully'], 200);
    }

    public function getUserPermissions(Request $request)
{
    $user = Auth::user();

    // Verifica si el usuario tiene un rol asignado
    if ($user->roles->isEmpty()) {
        return response()->json([
            'message' => 'El usuario no tiene rol asignado.'
        ], 400);
    }

    // Obtener el primer rol del usuario
    $role = $user->roles->first();

    // Obtener los permisos asignados al rol
    $permisos = $role->permissions->pluck('name'); // Los permisos del rol

    return response()->json([
        'permisos' => $permisos
    ]);
}
}
