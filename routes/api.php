<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

//PUBLIC ROUTES
Route::post('login', [AuthController::class, 'login']);


//PRIVATE ROUTES
Route::middleware([IsUserAuth::class])->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
        Route::get('me', 'getUser');
    });

    // Ruta para obtener todos los permisos de usuario
    Route::get('mis-permisos', [AuthController::class, 'getUserPermissions']);

    // Ruta para actualizar el perfil del usuario
    Route::put('me', [UserController::class, 'updateProfile']);

    // Ruta para cambiar la contraseña
    Route::put('change-password', [UserController::class, 'changePassword']);

    Route::middleware([IsAdmin::class])->group(function () {

        Route::get('users', [UserController::class, 'index']); // Mostrar todos los usuarios
        Route::post('users', [UserController::class, 'store']); // Crear un nuevo usuario
        Route::get('/users/search', [UserController::class, 'search']); // Búsqueda de usuarios
        Route::put('users/{id}', [UserController::class, 'update']); // Editar un usuario
        Route::delete('users/{id}', [UserController::class, 'destroy']); // Eliminar un usuario

         
         Route::get('roles', [RolePermissionController::class, 'indexRoles']);// Listar roles
         Route::post('roles', [RolePermissionController::class, 'storeRole']);// Crear un rol
         Route::delete('roles/{id}', [RolePermissionController::class, 'destroyRole']);// Eliminar un rol
         Route::put('roles/{roleId}/permissions', [RolePermissionController::class, 'assignPermissionsToRole']);// Asignar permisos a un rol
 
         
         Route::get('permissions', [RolePermissionController::class, 'indexPermissions']);// Listar permisos
         Route::post('permissions', [RolePermissionController::class, 'storePermission']);// Crear un permiso
         Route::delete('permissions/{id}', [RolePermissionController::class, 'destroyPermission']);// Eliminar un permiso
   

        // Ruta para asignar roles y permisos a un usuario
        Route::put('users/{userId}/roles-y-permisos', [UserController::class, 'asignarRolesYPermisos']);

        // Ruta para ver los permisos asignados a un rol
        Route::get('/roles/{roleId}/permissions', [RolePermissionController::class, 'getPermissionsForRole']);
    });
});
