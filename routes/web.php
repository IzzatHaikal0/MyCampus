<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\GradeController;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::get('/student/dashboard', fn() => view('student.dashboard'))->name('student.dashboard');
Route::get('/assignments', fn() => view('assignments'))->name('assignments');
Route::get('/schedule', fn() => view('schedule'))->name('schedule');
Route::get('/discussions', fn() => view('discussions'))->name('discussions');
Route::get('/module-4', fn() => view('module-4'))->name('module-4');

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


Route::get('/asssignments/view', [AssignmentController::class, 'viewStudentAssignment'])->name('assignments.viewStudentAssignment');
Route::post('/assignments/add-submission/{id}', [AssignmentController::class,'addSubmission'])->name('assignments.addSubmission');
Route::post('/assignments/edit-submission/{id}', [AssignmentController::class,'editSubmission'])->name('assignments.editSubmission');

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::get('/teacher/dashboard', [LessonController::class, 'teacherDashboard'])->name('teacher.dashboard');
Route::get('/lessons/add', [LessonController::class, 'create'])->name('lessons.add');
Route::post('/lessons/store', [LessonController::class, 'store'])->name('lessons.store');


Route::get('/assignments/list', [AssignmentController::class, 'list'])->name('assignments.list');
Route::get('/assignments/add', [AssignmentController::class, 'create'])->name('assignments.create');
Route::post('/assignments/store', [AssignmentController::class,'store'])->name('assignments.store');
Route::delete('/assignments/delete/{id}', [AssignmentController::class,'delete'])->name('assignments.delete');
Route::get('/assignments/edit/{id}', [AssignmentController::class, 'edit'])->name('assignments.edit');
Route::post('assignments/update/{id}', [AssignmentController::class,'update'])->name('assignments.update');
Route::get('/assignments/submission/{id}', [AssignmentController::class, 'viewListSubmission'])->name('submission-teacher.view');


Route::post('/assignments/grade-submission/{id}', [GradeController::class, 'addGrading'])->name('submissions.addGrading');
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/dashboard', fn() => view('admin.dashboard'))->name('admin.dashboard');

// Show Add Lesson page
Route::prefix('lessons')->group(function () {
    Route::get('/add', [LessonController::class, 'create'])->name('lessons.add');
    Route::post('/store', [LessonController::class, 'store'])->name('lessons.store');

    // AJAX route for overlap check
    Route::post('/check-overlap', [LessonController::class, 'checkOverlap'])->name('lessons.check-overlap');
});

// Teacher lesson management
Route::prefix('lessons')->group(function () {
    Route::get('/list', [LessonController::class, 'index'])->name('lessons.list');
    Route::get('/teacher/dashboard', [LessonController::class, 'teacherDashboard'])->name('teacher.dashboard');
    Route::get('/edit/{id}', [LessonController::class, 'edit'])->name('lessons.edit');
    Route::put('/update/{id}', [LessonController::class, 'update'])->name('lessons.update');  // ✅ This is important
    Route::delete('/delete/{id}', [LessonController::class, 'destroy'])->name('lessons.destroy');
    Route::get('/lessons/list', [LessonController::class, 'list'])->name('lessons.list');

});
