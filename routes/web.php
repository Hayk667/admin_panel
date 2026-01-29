<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;

/*
|--------------------------------------------------------------------------
| Web Routes (Frontend)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
Route::post('/page/{slug}/send-message', [PageController::class, 'sendMessage'])->name('page.send-message');
Route::get('/post/{slug}', [HomeController::class, 'show'])->name('post.show');
Route::post('/post/{post}/like', [HomeController::class, 'like'])->name('post.like');
Route::post('/post/{post}/rate', [HomeController::class, 'rate'])->name('post.rate');
