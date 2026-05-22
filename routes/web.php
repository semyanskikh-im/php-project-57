<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\TaskStatus;
use App\Http\Controllers\TaskStatusController;

Route::get('/', function () {
    return view('home');
})->name('home');

// ВРЕМЕННЫЕ ЗАГЛУШКИ (пока не созданы контроллеры)
Route::get('/tasks', function () {
    return 'Страница задач (будет позже)';
})->name('tasks.index');


Route::get('/labels', function () {
    return 'Страница меток (будет позже)';
})->name('labels.index');


// Дашборд после авторизации
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/task_statuses', [TaskStatusController::class, 'index'])->name('task_statuses.index');

Route::middleware('auth')->group(function () {
    Route::resource('task_statuses', TaskStatusController::class)->only([
        'create', 'store', 'edit', 'update', 'destroy'
    ]);
});

require __DIR__.'/auth.php';
