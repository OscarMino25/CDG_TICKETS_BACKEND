<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

//PUBLIC ROUTES
Route::post('login', [AuthController::class, 'login']);


//PRIVATE ROUTES
Route::middleware([IsUserAuth::class])->group(function(){
   
    Route::controller(AuthController::class)->group(function(){
        Route::post('logout', 'logout');
        Route::get('me', 'getUser');
    });

    // Ruta para actualizar el perfil del usuario
    Route::put('me', [UserController::class, 'updateProfile']);

    // Ruta para cambiar la contraseña
    Route::put('change-password', [UserController::class, 'changePassword']);

    Route::middleware([IsAdmin::class])->group(function(){

        Route::get('users', [UserController::class, 'index']); // Mostrar todos los usuarios
        Route::post('users', [UserController::class, 'store']); // Crear un nuevo usuario
        Route::get('/users/search', [UserController::class, 'search']); // Búsqueda de usuarios
        Route::put('users/{id}', [UserController::class, 'update']); // Editar un usuario
        Route::delete('users/{id}', [UserController::class, 'destroy']); // Eliminar un usuario
    });
});

