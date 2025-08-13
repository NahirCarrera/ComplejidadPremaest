<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\RecordController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\SubjectController;

Route::get('/', function () {
    return redirect()->route('login'); // Redirige a la ruta de login de Laravel
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Dashboard - Usando DashboardController
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    
    // Grupo para gestión de records académicos - Usando RecordController
    Route::prefix('records')->name('records.')->controller(RecordController::class)->group(function() {
        Route::get('/upload', 'showUploadRecordForm')->name('upload');
        Route::post('/process', 'processRecord')->name('process');
        Route::get('/approved', 'viewApprovedSubjects')->name('approved');
    });
    
    // Grupo para prematrícula - Usando EnrollmentController
    Route::prefix('pre-enrollment')->name('pre-enrollment.')->controller(EnrollmentController::class)->group(function() {
        Route::get('/', 'showAvailableSubjects')->name('plan');
        Route::post('/process', 'processPreEnrollment')->name('process');
        Route::delete('/planned-subject/{subject}', 'removePlannedSubject')->name('remove-planned-subject');
    });
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    
    // Períodos académicos (controlador dedicado)
    Route::prefix('periods')
        ->name('periods.')
        ->controller(PeriodController::class)
        ->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{period}/edit', 'edit')->name('edit');
            Route::put('/{period}', 'update')->name('update');
            Route::delete('/{period}', 'destroy')->name('destroy');
        });

    // Asignaturas (controlador dedicado)
    Route::prefix('subjects')
        ->name('subjects.')
        ->controller(SubjectController::class)
        ->group(function() {
            Route::get('/demand', 'subjectsDemand')->name('demand');
        });
});

require __DIR__.'/auth.php';