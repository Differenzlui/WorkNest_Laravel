<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MicrosoftController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tasks');

// Pages only for people who are NOT logged in
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/auth/microsoft/redirect', [MicrosoftController::class, 'redirect'])->name('microsoft.redirect');
    Route::get('/auth/microsoft/callback', [MicrosoftController::class, 'callback'])->name('microsoft.callback');
});

// Pages only for people who ARE logged in
Route::middleware('auth')->group(function () {
    Route::view('/tasks', 'tasks.index')->name('tasks.index');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});