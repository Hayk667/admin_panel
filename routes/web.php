<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes (Frontend)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/post/{slug}', [HomeController::class, 'show'])->name('post.show');
Route::post('/post/{post}/like', [HomeController::class, 'like'])->name('post.like');
Route::post('/post/{post}/rate', [HomeController::class, 'rate'])->name('post.rate');
