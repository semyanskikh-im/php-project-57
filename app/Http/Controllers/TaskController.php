<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TaskController extends Controller
{
    // Список задач (доступен всем)
    public function index()
    {
        $tasks = QueryBuilder::for(Task::class)
            ->allowedFilters(
                AllowedFilter::exact('status_id'),
                AllowedFilter::exact('created_by_id'),
                AllowedFilter::exact('assigned_to_id'),
            )
            ->with(['status', 'createdBy', 'assignedTo', 'labels'])
            ->paginate(15)
            ->appends(request()->query());

        $taskStatuses = TaskStatus::all();
        $users = User::all();

        return view('tasks.index', compact('tasks', 'taskStatuses', 'users'));
    }

    // Форма создания (только для авторизованных)
    public function create()
    {
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        $labels = Label::all();
        return view('tasks.create', compact('taskStatuses', 'users', 'labels'));
    }

    // Сохранение задачи
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assigned_to_id' => 'nullable|exists:users,id',
            'labels' => 'nullable|array',
            'labels.*' => 'exists:labels,id',
        ]);

        $validated['created_by_id'] = Auth::id();

        $task = Task::create($validated);

        // Привязываем метки
        if ($request->has('labels')) {
            $task->labels()->sync($request->labels);
        }

        flash()->success('Задача успешно создана');

        return redirect()->route('tasks.index');
    }

    public function show($id)
    {
        $task = Task::with('labels')->findOrFail($id);
        return view('tasks.show', compact('task'));
    }

    public function edit($id)
    {
        $task = Task::with('labels')->findOrFail($id);
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        $labels = Label::all();
        return view('tasks.edit', compact('task', 'taskStatuses', 'users', 'labels'));
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assigned_to_id' => 'nullable|exists:users,id',
            'labels' => 'nullable|array',
            'labels.*' => 'exists:labels,id',
        ]);

        $task->update($validated);

        // Обновляем метки
        if ($request->has('labels')) {
            $task->labels()->sync($request->labels);
        } else {
            $task->labels()->sync([]);
        }

        flash()->success('Задача успешно изменена');

        return redirect()->route('tasks.index');
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        if ($task->created_by_id !== Auth::id()) {
            abort(403, 'This action is unauthorized.');
        }

        // Отвязываем метки перед удалением задачи
        $task->labels()->detach();

        $task->delete();

        flash()->success('Задача успешно удалена');

        return redirect()->route('tasks.index');
    }
}
