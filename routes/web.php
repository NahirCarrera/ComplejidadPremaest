<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
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

Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    
    // Grupo para gestión de records académicos
    Route::prefix('records')->name('records.')->group(function() {
        Route::get('/upload', [StudentController::class, 'showUploadRecordForm'])->name('upload');
        Route::post('/process', [StudentController::class, 'processRecord'])->name('process');
        Route::get('/approved', [StudentController::class, 'viewApprovedSubjects'])->name('approved');
    });
    
    // Grupo para prematrícula
    Route::prefix('pre-enrollment')->name('pre-enrollment.')->group(function() {
        Route::get('/', [StudentController::class, 'showAvailableSubjects'])->name('plan');
        Route::post('/process', [StudentController::class, 'processPreEnrollment'])->name('process');
        Route::delete('/planned-subject/{subject}', [StudentController::class, 'removePlannedSubject'])
            ->name('remove-planned-subject');
    });
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Períodos académicos (controlador dedicado)
    Route::prefix('periods')
        ->name('periods.')
        ->controller(AdminController::class) // Especifica el controlador aquí
        ->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{period}/edit', 'edit')->name('edit');
            Route::put('/{period}', 'update')->name('update');
            Route::delete('/{period}', 'destroy')->name('destroy');
        });
    Route::prefix('subjects')
    ->name('subjects.')
    ->group(function() {
        Route::get('/demand', [AdminController::class, 'subjectsDemand'])->name('demand');
    });
});
require __DIR__.'/auth.php';
