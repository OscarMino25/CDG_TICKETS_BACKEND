<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\TipificacionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\SlaController;
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

    // Ruta para ver los permisos asignados a un rol
    Route::get('/roles/{roleId}/permissions', [RolePermissionController::class, 'getPermissionsForRole']);

    Route::get('users', [UserController::class, 'index']); // Mostrar todos los usuarios


    Route::middleware([IsAdmin::class])->group(function () {


        Route::post('users', [UserController::class, 'store']); // Crear un nuevo usuario
        Route::get('/users/search', [UserController::class, 'search']); // Búsqueda de usuarios
        Route::put('users/{id}', [UserController::class, 'update']); // Editar un usuario
        Route::delete('users/{id}', [UserController::class, 'destroy']); // Eliminar un usuario


        Route::get('roles', [RolePermissionController::class, 'indexRoles']); // Listar roles
        Route::post('roles', [RolePermissionController::class, 'storeRole']); // Crear un rol
        Route::delete('roles/{id}', [RolePermissionController::class, 'destroyRole']); // Eliminar un rol
        Route::put('roles/{roleId}/permissions', [RolePermissionController::class, 'assignPermissionsToRole']); // Asignar permisos a un rol


        Route::get('permissions', [RolePermissionController::class, 'indexPermissions']); // Listar permisos
        Route::post('permissions', [RolePermissionController::class, 'storePermission']); // Crear un permiso
        Route::delete('permissions/{id}', [RolePermissionController::class, 'destroyPermission']); // Eliminar un permiso


        // Ruta para asignar roles y permisos a un usuario
        Route::put('users/{userId}/roles-y-permisos', [UserController::class, 'asignarRolesYPermisos']);

        // Rutas del controlador CatalogoController
        Route::get('catalogos', [CatalogoController::class, 'index']); // Mostrar todos los catálogos
        Route::post('catalogos', [CatalogoController::class, 'store']); // Crear un nuevo catálogo
        Route::get('catalogos/{id}', [CatalogoController::class, 'show']); // Ver un catálogo específico
        Route::put('catalogos/{id}', [CatalogoController::class, 'update']); // Actualizar un catálogo
        Route::delete('catalogos/{id}', [CatalogoController::class, 'destroy']); // Eliminar un catálogo

        // Rutas del controlador TipificacionController
        Route::get('tipificaciones', [TipificacionController::class, 'index']); // Mostrar todas las tipificaciones
        Route::post('tipificaciones', [TipificacionController::class, 'store']); // Crear una nueva tipificación
        Route::get('tipificaciones/{id}', [TipificacionController::class, 'show']); // Ver una tipificación específica
        Route::put('tipificaciones/{id}', [TipificacionController::class, 'update']); // Actualizar una tipificación
        Route::delete('tipificaciones/{id}', [TipificacionController::class, 'destroy']); // Eliminar una tipificación

        Route::get('slas', [SlaController::class, 'index']); // Obtener todos los SLAs
        Route::post('slas', [SlaController::class, 'store']); // Crear un nuevo SLA
        Route::get('slas/{id}', [SlaController::class, 'show']); // Obtener un SLA por ID
        Route::put('slas/{id}', [SlaController::class, 'update']); // Actualizar un SLA
        Route::delete('slas/{id}', [SlaController::class, 'destroy']); // Eliminar un SLA

    });
});
