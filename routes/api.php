<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryConroller;
use App\Http\Controllers\CountController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



//public route
Route::post('login',[AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::apiResource('/books',BookController::class);


//protected route
Route::middleware('jwt.auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);

    Route::apiResource('/category',CategoryConroller::class);
    Route::apiResource('/permissions',PermissionController::class);
    Route::apiResource('count',CountController::class);
    Route::prefix('users')->group(function () {
            Route::get('/list', [UserController::class, 'index']);
            Route::post('/create', [UserController::class, 'store']);
            Route::get('/show/{id}', [UserController::class, 'show']);
            Route::put('/update/{id}', [UserController::class, 'update']);
            Route::put('/update_password/{id}', [UserController::class, 'updatePassword']);
            Route::delete('/delete/{id}', [UserController::class, 'destroy']);
            Route::put('/update_status/{id}', [UserController::class, 'UpdateStatus']);
            Route::post('/upload_profile', [ProfileController::class, 'uploadProfileImage']);

        });
         Route::prefix('roles')->group(function () {
            Route::get('/list', [RolesController::class, 'index']);
            Route::post('/create', [RolesController::class, 'store']);
            Route::get('/show/{id}', [RolesController::class, 'show']);
            Route::get('/edit/{id}', [RolesController::class, 'edit']);
            Route::put('/update/{id}', [RolesController::class, 'update']);
            Route::get('/get_persimssion', [RolesController::class, 'getPermission']);
            Route::delete('/delete/{id}', [RolesController::class, 'destroy']);
        });
});
