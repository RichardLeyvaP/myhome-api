<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('login-apk', [AuthController::class, 'loginApk'])->name('loginApk');
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
    Route::post('/select-language', [UserController::class, 'selectLanguage']);
    /*Route::post('/select-language', function (Request $request) {
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
    });*/
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    //Usuario
    Route::get('user', [UserController::class, 'index'])->name('user');
    Route::get('get-user-language-changes', [UserController::class, 'getUserLanguageChanges'])->name('getUserLanguageChanges');

    //Roles
    Route::get('role', [RoleController::class, 'index'])->name('index');
    Route::post('role', [RoleController::class, 'store'])->name('store');
    Route::get('role-show', [RoleController::class, 'show'])->name('show');
    Route::put('role', [RoleController::class, 'update'])->name('update');
    Route::post('role-destroy', [RoleController::class, 'destroy'])->name('destroy');

    //Categories
    Route::get('category', [CategoryController::class, 'index'])->name('index');
    Route::post('category', [CategoryController::class, 'store'])->name('store');
    Route::get('category-show', [CategoryController::class, 'show'])->name('show');
    Route::post('category-updated', [CategoryController::class, 'update'])->name('update');
    Route::post('category-destroy', [CategoryController::class, 'destroy'])->name('destroy');

    //Categories
    Route::get('status', [StatusController::class, 'index'])->name('index');
    Route::post('status', [StatusController::class, 'store'])->name('store');
    Route::get('status-show', [StatusController::class, 'show'])->name('show');
    Route::put('status', [StatusController::class, 'update'])->name('update');
    Route::post('status-destroy', [StatusController::class, 'destroy'])->name('destroy');

    //Priorities
    Route::get('priority', [PriorityController::class, 'index'])->name('index');
    Route::post('priority', [PriorityController::class, 'store'])->name('store');
    Route::get('priority-show', [PriorityController::class, 'show'])->name('show');
    Route::put('priority', [PriorityController::class, 'update'])->name('update');
    Route::post('priority-destroy', [PriorityController::class, 'destroy'])->name('destroy');

    //tasks
    Route::get('task', [TaskController::class, 'index'])->name('index');
    Route::post('task', [TaskController::class, 'store'])->name('store');
    Route::get('task-show', [TaskController::class, 'show'])->name('show');
    Route::post('task-updated', [TaskController::class, 'update'])->name('update');
    Route::post('task-destroy', [TaskController::class, 'destroy'])->name('destroy');
    Route::get('task-history', [TaskController::class, 'getTaskHistory'])->name('getTaskHistory');
    Route::get('task-date-apk', [TaskController::class, 'getTaskDate'])->name('getTaskDate');
});
