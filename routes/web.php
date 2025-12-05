<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LessonController;

// Root
Route::get('/', [AuthController::class, 'showRegisterForm']);

// Auth
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard for all users
Route::middleware(['auth.firebase'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

// Teacher-only routes
Route::middleware(['auth.firebase:teacher'])->group(function () {
    Route::get('/lessons/add', [LessonController::class, 'create'])->name('lessons.add');
    Route::post('/lessons/store', [LessonController::class, 'store'])->name('lessons.store');
});

// Admin-only routes
Route::middleware(['auth.firebase:administrator'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});
