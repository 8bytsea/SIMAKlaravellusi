<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('students.index');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'nocache'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('students.index');
    })->name('dashboard');

    Route::get('/students/backup', [StudentController::class, 'backup'])
        ->name('students.backup');

    Route::get('/students', [StudentController::class, 'index'])
        ->name('students.index');

    Route::post('/students', [StudentController::class, 'store'])
        ->name('students.store');

    Route::put('/students/{id}', [StudentController::class, 'update'])
        ->name('students.update');

    Route::delete('/students/{id}', [StudentController::class, 'destroy'])
        ->name('students.destroy');
});

require __DIR__.'/auth.php';