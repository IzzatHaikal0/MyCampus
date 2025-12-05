<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
