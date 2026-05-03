<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', [AdminController::class, 'dashboard']);
Route::get('/admin/users', [AdminController::class, 'users']);
Route::get('/admin/users/create', [AdminController::class, 'createUser']);
Route::post('/admin/users', [AdminController::class, 'storeUser']);
Route::get('/admin/users/{user}/edit', [AdminController::class, 'editUser']);
Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser']);
