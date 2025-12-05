<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Example routes to add to routes/web.php
Route::get('/assignments', function () {
    return view('assignments'); // Create this Blade view
})->middleware(['auth', 'verified'])->name('assignments');

Route::get('/schedule', function () {
    return view('schedule'); // Create this Blade view
})->middleware(['auth', 'verified'])->name('schedule');

Route::get('/discussions', function () {
    return view('discussions'); // Create this Blade view
})->middleware(['auth', 'verified'])->name('discussions');

Route::get('/module-4', function () {
    return view('module-4'); // Create this Blade view
})->middleware(['auth', 'verified'])->name('module-4');

require __DIR__.'/auth.php';
