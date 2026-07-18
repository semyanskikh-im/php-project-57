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

// Статусы
Route::get('/task_statuses', [TaskStatusController::class, 'index'])->name('task_statuses.index');
Route::resource('task_statuses', TaskStatusController::class)->except(['index']);

// Задачи
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show')->where('id', '[0-9]+');
Route::resource('tasks', TaskController::class)->except(['index', 'show']);

// Метки
Route::get('/labels', [LabelController::class, 'index'])->name('labels.index');
Route::get('/labels/{id}', [LabelController::class, 'show'])->name('labels.show')->where('id', '[0-9]+');
Route::resource('labels', LabelController::class)->except(['index', 'show']);

require __DIR__ . '/auth.php';
