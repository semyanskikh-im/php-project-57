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
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Список задач (доступен всем)
     */
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

    /**
     * Форма создания задачи (только для авторизованных)
     */
    public function create()
    {
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        $labels = Label::all();
        return view('tasks.create', compact('taskStatuses', 'users', 'labels'));
    }

    /**
     * Сохранение новой задачи
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assigned_to_id' => 'nullable|exists:users,id',
            'labels' => 'nullable|array',
            'labels.*' => 'exists:labels,id',
        ], [
            'name.required' => 'Это обязательное поле',
            'status_id.required' => 'Это обязательное поле',
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

    /**
     * Просмотр конкретной задачи (доступен всем)
     */
    public function show($id)
    {
        $task = Task::with('labels')->findOrFail($id);
        return view('tasks.show', compact('task'));
    }

    /**
     * Форма редактирования задачи (только для авторизованных)
     */
    public function edit($id)
    {
        $task = Task::with('labels')->findOrFail($id);
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        $labels = Label::all();
        return view('tasks.edit', compact('task', 'taskStatuses', 'users', 'labels'));
    }

    /**
     * Обновление задачи
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Проверка прав через Policy
        $this->authorize('update', $task);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assigned_to_id' => 'nullable|exists:users,id',
            'labels' => 'nullable|array',
            'labels.*' => 'exists:labels,id',
        ], [
            'name.required' => 'Это обязательное поле',
            'status_id.required' => 'Это обязательное поле',
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

    /**
     * Удаление задачи
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // Проверка прав через Policy
        $this->authorize('delete', $task);

        // Отвязываем метки перед удалением задачи
        $task->labels()->detach();

        $task->delete();

        flash()->success('Задача успешно удалена');

        return redirect()->route('tasks.index');
    }
}
