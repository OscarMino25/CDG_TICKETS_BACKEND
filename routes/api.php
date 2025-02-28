<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

//PUBLIC ROUTES
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


//PRIVATE ROUTES
Route::middleware([IsUserAuth::class])->group(function(){
   
    Route::controller(AuthController::class)->group(function(){
        Route::post('logout', 'logout');
        Route::get('me', 'getUser');
    });
    Route::get('Products', [ProductController::class, 'getProducts']);

    Route::middleware([IsAdmin::class])->group(function(){
        Route::controller(ProductController::class)->group(function(){
            Route::post('/products', 'addProduct');
            Route::get('/products/{id}', 'getProductById');
            Route::patch('/products/{id}', 'updateProductById');
            Route::delete('/products/{id}', 'deleteProductById');
        });
    });
});

