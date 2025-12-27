<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\StudyGroupController;

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
Route:: get('/assignments/view-grades/', [GradeController::class, 'viewGrade'])->name('assignments.viewGrade');
/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::get('/teacher/dashboard', action: fn() => view('teacher.dashboard'))->name('teacher.dashboard');
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
Route::post('/assignments/grade-submission/edit/{id}', [GradeController::class, 'editGrading'])->name('assignments.editGrading');
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/dashboard', fn() => view('admin.dashboard'))->name('admin.dashboard');

/*
|-------------------------------------------------------------------------- 
| Study Group Routes
|-------------------------------------------------------------------------- 
*/
// List semua group (index)
Route::get('/study-groups', [StudyGroupController::class, 'index'])->name('study-groups.index');

// Create group
Route::get('/study-groups/create', [StudyGroupController::class, 'create'])->name('study-groups.create');
Route::post('/study-groups/store', [StudyGroupController::class, 'store'])->name('study-groups.store');

// Edit & Update
// Edit & Update
Route::get('/study-groups/{study_group}/edit', [StudyGroupController::class, 'edit'])->name('study-groups.edit');
Route::put('/study-groups/{study_group}/update', [StudyGroupController::class, 'update'])->name('study-groups.update');

// Delete group
Route::delete('/study-groups/{study_group}', [StudyGroupController::class, 'destroy'])->name('study-groups.destroy');

// Routes dengan parameter {study_group}
Route::prefix('study-groups')->group(function () {
    Route::get('/{study_group}/chat', [StudyGroupController::class, 'chat'])->name('study-groups.chat');
    Route::post('/{study_group}/send-message', [StudyGroupController::class, 'sendMessage'])->name('study-groups.sendMessage');
    Route::post('/{study_group}/join', [StudyGroupController::class, 'join'])->name('study-groups.join');
    Route::post('/join-by-code', [StudyGroupController::class, 'joinByCode'])->name('study-groups.joinByCode');
});
