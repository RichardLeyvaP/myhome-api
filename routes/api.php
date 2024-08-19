<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
Route::get('/google-callback-apk', [AuthController::class, 'googleCallbackApk']);
Route::get('/facebook-callback-apk', [AuthController::class, 'facebookCallbackApk']);

Route::group( ['middleware' => ["auth:sanctum"]], function(){
    //Seleccionar Idioma
    Route::post('/select-language', function (Request $request) {
    $user = Auth::user();
    $locale = $request->input('locale');

    if (!in_array($locale, ['en', 'es', 'pt'])) {
        $locale = 'es';
    }
    Log::info('Idioma seleccionado');
    Log::info($locale);
    // Actualizar el idioma del usuario
    $user->language = $locale;
    $user->save();

    App::setLocale($locale);
    session(['locale' => $locale]);

    return response()->json(['message' => __('Idioma seleccionado correctamente.')]);
    });
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
