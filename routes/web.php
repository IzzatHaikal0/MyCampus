<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudyGroupController;
use App\Http\Controllers\CommunicationHubController;
use App\Http\Controllers\CommunicationChatController;

use App\Http\Middleware\SessionMiddleware;

/*
|--------------------------------------------------------------------------
| Root & Authentication
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Session Protected)
|--------------------------------------------------------------------------
*/
Route::middleware([SessionMiddleware::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:student'])->group(function () {

        Route::get('/student/dashboard', [LessonController::class, 'studentDashboard'])->name('student.dashboard');
        Route::get('/student/timetable', [LessonController::class, 'studentTimetable'])->name('student.timetable');
        Route::get('/student/timetable/pdf', [LessonController::class, 'downloadTimetablePdf'])->name('student.timetable.pdf');

        Route::get('/student/grades', [GradeController::class, 'viewGrade'])->name('assignments.viewGrade');

        Route::get('/notifications', [LessonController::class, 'studentNotifications'])->name('notifications.list');
        Route::post('/notifications/mark-read/{id}', [LessonController::class, 'markNotificationRead'])->name('notifications.markRead');

        // Simple Views
        Route::get('/assignments', fn () => view('assignments'))->name('assignments');
        Route::get('/schedule', fn () => view('schedule'))->name('schedule');
        Route::get('/discussions', fn () => view('discussions'))->name('discussions');

        // Assignment Actions
        Route::get('/assignments/view', [AssignmentController::class, 'viewStudentAssignment'])
            ->name('assignments.viewStudentAssignment');

        Route::post('/assignments/add-submission/{id}', [AssignmentController::class, 'addSubmission'])
            ->name('assignments.addSubmission');

        Route::post('/assignments/edit-submission/{id}', [AssignmentController::class, 'editSubmission'])
            ->name('assignments.editSubmission');
    });

    /*
    |--------------------------------------------------------------------------
    | Teacher Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:teacher'])->group(function () {

        Route::get('/teacher/dashboard', [LessonController::class, 'teacherDashboard'])->name('teacher.dashboard');

        // Lesson Management
        Route::prefix('lessons')->group(function () {
            Route::get('/list', [LessonController::class, 'index'])->name('lessons.list');
            Route::get('/add', [LessonController::class, 'create'])->name('lessons.add');
            Route::post('/store', [LessonController::class, 'store'])->name('lessons.store');
            Route::get('/edit/{id}', [LessonController::class, 'edit'])->name('lessons.edit');
            Route::put('/update/{id}', [LessonController::class, 'update'])->name('lessons.update');
            Route::delete('/delete/{id}', [LessonController::class, 'destroy'])->name('lessons.destroy');
            Route::post('/check-overlap', [LessonController::class, 'checkOverlap'])->name('lessons.check-overlap');
        });

        // Assignment Management
        Route::get('/assignments/list', [AssignmentController::class, 'list'])->name('assignments.list');
        Route::get('/assignments/add', [AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments/store', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/assignments/edit/{id}', [AssignmentController::class, 'edit'])->name('assignments.edit');
        Route::post('/assignments/update/{id}', [AssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/assignments/delete/{id}', [AssignmentController::class, 'delete'])->name('assignments.delete');
        Route::get('/assignments/submission/{id}', [AssignmentController::class, 'viewListSubmission'])
            ->name('submission-teacher.view');

        // Grading
        Route::post('/assignments/grade-submission/{id}', [GradeController::class, 'addGrading'])
            ->name('submissions.addGrading');

        Route::post('/assignments/edit-grade/{id}', [GradeController::class, 'editGrading'])
            ->name('submissions.editGrading');
    });

    /*
    |--------------------------------------------------------------------------
    | Communication Hub (All Auth Users)
    |--------------------------------------------------------------------------
    */
    Route::get('/communication-hub', [CommunicationHubController::class, 'index'])
        ->name('communication.hub');

    Route::post('/chat/send', [CommunicationChatController::class, 'send'])
        ->name('chat.send');

    /*
    |--------------------------------------------------------------------------
    | Admin & Utility
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
    Route::get('/fix-lessons', [LessonController::class, 'autoFixLessonRepeats']);

    /*
    |--------------------------------------------------------------------------
    | Profile (Shared)
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile.edit');
    Route::patch('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [AuthController::class, 'deleteAccount'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Study Group Routes (Student Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:student'])->group(function () {

    Route::get('/study-groups', [StudyGroupController::class, 'index'])->name('study-groups.index');
    Route::get('/study-groups/create', [StudyGroupController::class, 'create'])->name('study-groups.create');
    Route::post('/study-groups', [StudyGroupController::class, 'store'])->name('study-groups.store');
    Route::post('/study-groups/join', [StudyGroupController::class, 'joinByCode'])->name('study-groups.joinByCode');

    Route::get('/study-groups/{groupId}/chat', [StudyGroupController::class, 'chat'])->name('study-groups.chat');
    Route::post('/study-groups/{groupId}/message', [StudyGroupController::class, 'sendMessage'])->name('study-groups.message');

    Route::get('/study-groups/{groupId}/edit', [StudyGroupController::class, 'edit'])->name('study-groups.edit');
    Route::put('/study-groups/{groupId}', [StudyGroupController::class, 'update'])->name('study-groups.update');
    Route::delete('/study-groups/{groupId}', [StudyGroupController::class, 'destroy'])->name('study-groups.destroy');
});
