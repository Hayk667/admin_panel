<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\MenuController;

/*
|--------------------------------------------------------------------------
| Admin Routes (prefix: /admin)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Languages CRUD
    Route::resource('languages', LanguageController::class);

    // Categories CRUD
    Route::resource('categories', CategoryController::class);

    // Tags CRUD
    Route::resource('tags', TagController::class)->except(['show']);

    // Posts CRUD
    Route::resource('posts', PostController::class);

    // Pages CRUD
    Route::resource('pages', PageController::class)->except(['show']);

    // Menu (reorder active pages)
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::post('/menu/reorder', [MenuController::class, 'reorder'])->name('menu.reorder');

    // Image Upload
    Route::post('/upload/image', [ImageUploadController::class, 'upload'])->name('upload.image');
    Route::post('/upload/cleanup', [ImageUploadController::class, 'cleanup'])->name('upload.cleanup');
    Route::post('/upload/clear-tracking', [ImageUploadController::class, 'clearTracking'])->name('upload.clear-tracking');

    // Users (read-only with edit)
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

    // Roles & Permissions (admin only)
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'index'])->name('index');
        Route::get('/{role}/edit', [RolePermissionController::class, 'editRole'])->name('edit');
        Route::put('/{role}', [RolePermissionController::class, 'updateRole'])->name('update');
    });

    // Permissions
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::post('/', [RolePermissionController::class, 'storePermission'])->name('store');
        Route::delete('/{permission}', [RolePermissionController::class, 'destroyPermission'])->name('destroy');
    });

    // Page Permissions
    Route::prefix('page-permissions')->name('page-permissions.')->group(function () {
        Route::post('/', [RolePermissionController::class, 'storePagePermission'])->name('store');
        Route::get('/{pagePermission}/edit', [RolePermissionController::class, 'editPagePermission'])->name('edit');
        Route::put('/{pagePermission}', [RolePermissionController::class, 'updatePagePermission'])->name('update');
        Route::delete('/{pagePermission}', [RolePermissionController::class, 'destroyPagePermission'])->name('destroy');
    });
});
