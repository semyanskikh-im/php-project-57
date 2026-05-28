<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // Список задач (доступен всем)
    public function index()
    {
        $tasks = Task::with(['status', 'createdBy', 'assignedTo'])->paginate(15);
        return view('tasks.index', compact('tasks'));
    }

    // Форма создания (только для авторизованных)
    public function create()
    {

        $taskStatuses = TaskStatus::all();
        $users = User::all();
        return view('tasks.create', compact('taskStatuses', 'users'));
    }

    // Сохранение задачи
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assigned_to_id' => 'nullable|exists:users,id',
        ]);

        $validated['created_by_id'] = Auth::id();

        Task::create($validated);

        flash()->success('Задача успешно создана');

        return redirect()->route('tasks.index');
    }

    public function show($id)
    {
        $task = Task::findOrFail($id);
        return view('tasks.show', compact('task'));
    }

    public function edit($id)
    {
        $task = Task::findOrFail($id);
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        return view('tasks.edit', compact('task', 'taskStatuses', 'users'));
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assigned_to_id' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        flash()->success('Задача успешно изменена');

        return redirect()->route('tasks.index');
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        if ($task->created_by_id !== Auth::id()) {
            abort(403, 'This action is unauthorized.');
        }

        $task->delete();

        flash()->success('Задача успешно удалена');

        return redirect()->route('tasks.index');
    }
}
