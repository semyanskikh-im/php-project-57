<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// ВРЕМЕННЫЕ ЗАГЛУШКИ (пока не созданы контроллеры)
Route::get('/tasks', function () {
    return 'Страница задач (будет позже)';
})->name('tasks.index');

Route::get('/task_statuses', function () {
    return 'Страница статусов (будет позже)';
})->name('task_statuses.index');

Route::get('/labels', function () {
    return 'Страница меток (будет позже)';
})->name('labels.index');


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
