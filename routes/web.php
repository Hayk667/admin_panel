<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/post/{slug}', [\App\Http\Controllers\HomeController::class, 'show'])->name('post.show');
Route::post('/post/{post}/like', [\App\Http\Controllers\HomeController::class, 'like'])->name('post.like');
Route::post('/post/{post}/rate', [\App\Http\Controllers\HomeController::class, 'rate'])->name('post.rate');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Languages CRUD
    Route::resource('languages', \App\Http\Controllers\LanguageController::class);
    
    // Categories CRUD
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    
    // Posts CRUD
    Route::resource('posts', \App\Http\Controllers\PostController::class);
    
    // Image Upload
    Route::post('/upload/image', [\App\Http\Controllers\ImageUploadController::class, 'upload'])->name('upload.image');
    Route::post('/upload/cleanup', [\App\Http\Controllers\ImageUploadController::class, 'cleanup'])->name('upload.cleanup');
    Route::post('/upload/clear-tracking', [\App\Http\Controllers\ImageUploadController::class, 'clearTracking'])->name('upload.clear-tracking');
    
    // Users (read-only with edit)
    Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    
    // Roles & Permissions (admin only)
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RolePermissionController::class, 'index'])->name('index');
        Route::get('/{role}/edit', [\App\Http\Controllers\RolePermissionController::class, 'editRole'])->name('edit');
        Route::put('/{role}', [\App\Http\Controllers\RolePermissionController::class, 'updateRole'])->name('update');
    });
    
    // Permissions
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::post('/', [\App\Http\Controllers\RolePermissionController::class, 'storePermission'])->name('store');
        Route::delete('/{permission}', [\App\Http\Controllers\RolePermissionController::class, 'destroyPermission'])->name('destroy');
    });
    
    // Page Permissions
    Route::prefix('page-permissions')->name('page-permissions.')->group(function () {
        Route::post('/', [\App\Http\Controllers\RolePermissionController::class, 'storePagePermission'])->name('store');
        Route::get('/{pagePermission}/edit', [\App\Http\Controllers\RolePermissionController::class, 'editPagePermission'])->name('edit');
        Route::put('/{pagePermission}', [\App\Http\Controllers\RolePermissionController::class, 'updatePagePermission'])->name('update');
        Route::delete('/{pagePermission}', [\App\Http\Controllers\RolePermissionController::class, 'destroyPagePermission'])->name('destroy');
    });
});
