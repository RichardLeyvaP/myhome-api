<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [UserController::class, 'register'])->name('register');

Route::get('/login-google', function () {
    return Socialite::driver('google')->stateless()->redirect();
});

Route::get('/login-facebook', function () {
    return Socialite::driver('facebook')->stateless()->redirect();
});
Route::get('/google-callback', [AuthController::class, 'googleCallback']);
Route::get('/facebook-callback', [AuthController::class, 'facebookCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    //Usuario
    Route::get('user', [UserController::class, 'index'])->name('user');

    //Roles
    Route::get('role', [RoleController::class, 'index'])->name('index');
    Route::post('role', [RoleController::class, 'store'])->name('store');
    Route::get('role-show', [RoleController::class, 'show'])->name('show');
    Route::put('role', [RoleController::class, 'update'])->name('update');
    Route::post('role-destroy', [RoleController::class, 'destroy'])->name('destroy');
});
