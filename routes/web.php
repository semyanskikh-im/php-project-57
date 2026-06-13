<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LabelController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Cтатусы (доступно всем)
Route::get('/task_statuses', [TaskStatusController::class, 'index'])->name('task_statuses.index');

// Статусы (доступны для авторизованных)
Route::middleware('auth')->group(function () {
    Route::resource('task_statuses', TaskStatusController::class)->only([
        'create',
        'store',
        'edit',
        'update',
        'destroy'
    ]);
});

// Задачи (доступны всем)
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show')->where('id', '[0-9]+');

// Задачи (только для авторизованных)
Route::middleware('auth')->group(function () {
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{id}/edit', [TaskController::class, 'edit'])->name('tasks.edit')->where('id', '[0-9]+');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update')->where('id', '[0-9]+');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy')->where('id', '[0-9]+');
});

// Доступен всем
Route::get('/labels', [LabelController::class, 'index'])->name('labels.index');
Route::get('/labels/{id}', [LabelController::class, 'show'])->name('labels.show')->where('id', '[0-9]+');

// Только для авторизованных
Route::middleware('auth')->group(function () {
    Route::get('/labels/create', [LabelController::class, 'create'])->name('labels.create');
    Route::post('/labels', [LabelController::class, 'store'])->name('labels.store');
    Route::get('/labels/{id}/edit', [LabelController::class, 'edit'])->name('labels.edit')->where('id', '[0-9]+');
    Route::put('/labels/{id}', [LabelController::class, 'update'])->name('labels.update')->where('id', '[0-9]+');
    Route::delete('/labels/{id}', [LabelController::class, 'destroy'])->name('labels.destroy')->where('id', '[0-9]+');
});

require __DIR__ . '/auth.php';
