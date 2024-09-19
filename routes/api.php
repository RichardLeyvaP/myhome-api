<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomeTypeController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStatusController;
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

Route::post('login', [AuthController::class, 'login']);
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
    Route::post('logout', [AuthController::class, 'logout']);
    
    //Usuario
    Route::get('user', [UserController::class, 'index']);
    Route::get('get-user-language-changes', [UserController::class, 'getUserLanguageChanges']);

    //Roles
    Route::get('role', [RoleController::class, 'index']);
    Route::post('role', [RoleController::class, 'store']);
    Route::get('role-show', [RoleController::class, 'show']);
    Route::put('role', [RoleController::class, 'update']);
    Route::post('role-destroy', [RoleController::class, 'destroy']);

    //Categories
    Route::get('category', [CategoryController::class, 'index']);
    Route::post('category', [CategoryController::class, 'store']);
    Route::get('category-show', [CategoryController::class, 'show']);
    Route::post('category-updated', [CategoryController::class, 'update']);
    Route::post('category-destroy', [CategoryController::class, 'destroy']);

    //Categories
    Route::get('status', [StatusController::class, 'index']);
    Route::post('status', [StatusController::class, 'store']);
    Route::get('status-show', [StatusController::class, 'show']);
    Route::put('status', [StatusController::class, 'update']);
    Route::post('status-destroy', [StatusController::class, 'destroy']);

    //Priorities
    Route::get('priority', [PriorityController::class, 'index']);
    Route::post('priority', [PriorityController::class, 'store']);
    Route::get('priority-show', [PriorityController::class, 'show']);
    Route::put('priority', [PriorityController::class, 'update']);
    Route::post('priority-destroy', [PriorityController::class, 'destroy']);

    //tasks
    Route::get('task', [TaskController::class, 'index']);
    Route::post('task', [TaskController::class, 'store']);
    Route::get('task-show', [TaskController::class, 'show']);
    Route::post('task-updated', [TaskController::class, 'update']);
    Route::post('task-destroy', [TaskController::class, 'destroy']);
    Route::get('task-history', [TaskController::class, 'getTaskHistory']);
    Route::get('task-date-apk', [TaskController::class, 'getTaskDate']);

    // Rutas para ProductCategory
    Route::get('product-categories', [ProductCategoryController::class, 'index']);
    Route::post('product-categories', [ProductCategoryController::class, 'store']);
    Route::get('product-categories-show', [ProductCategoryController::class, 'show']);
    Route::put('product-categories', [ProductCategoryController::class, 'update']);
    Route::post('product-categories-destroy', [ProductCategoryController::class, 'destroy']);

    // Rutas para ProductStatus
    Route::get('product-statuses', [ProductStatusController::class, 'index']);
    Route::post('product-statuses', [ProductStatusController::class, 'store']);
    Route::get('product-statuses-show', [ProductStatusController::class, 'show']);
    Route::put('product-statuses', [ProductStatusController::class, 'update']);
    Route::post('product-statuses-destroy', [ProductStatusController::class, 'destroy']);

    // Rutas para Product
    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products-show', [ProductController::class, 'show']);
    Route::post('products-update', [ProductController::class, 'update']);
    Route::post('products-destroy', [ProductController::class, 'destroy']);

    // Rutas para people
    Route::get('person', [PersonController::class, 'index']);
    Route::post('person', [PersonController::class, 'store']);
    Route::get('person-show', [PersonController::class, 'show']);
    Route::post('person-update', [PersonController::class, 'update']);
    Route::post('person-destroy', [ProductController::class, 'destroy']);
    
    // Rutas para ProductStatus
    Route::get('home-type', [HomeTypeController::class, 'index']);
    Route::post('home-type', [HomeTypeController::class, 'store']);
    Route::get('home-type-show', [HomeTypeController::class, 'show']);
    Route::put('home-type', [HomeTypeController::class, 'update']);
    Route::post('home-type-destroy', [HomeTypeController::class, 'destroy']);

    // Rutas para Homes
    Route::get('home', [HomeController::class, 'index']);
    Route::post('home', [HomeController::class, 'store']);
    Route::get('home-show', [HomeController::class, 'show']);
    Route::post('home-update', [HomeController::class, 'update']);
    Route::post('home-destroy', [HomeController::class, 'destroy']);


    //rutas unificadas
    Route::get('productcategory-productstatus-apk', [ProductController::class, 'productcategory_productstatus']);
    Route::get('category-status-priority-apk', [TaskController::class, 'category_status_priority']);
});
