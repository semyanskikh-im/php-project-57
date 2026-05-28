<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TaskStatus;

class TaskStatusController extends Controller
{
    public function index()
    {
        $taskStatuses = TaskStatus::paginate(15);
        return view('task_statuses.index', compact('taskStatuses'));
    }

    public function create()
    {
        return view('task_statuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:task_statuses|min:1|max:255',
        ]);

        TaskStatus::create($validated);

        flash()->success('Статус успешно создан');

        return redirect()->route('task_statuses.index');
    }

    public function edit(TaskStatus $taskStatus)
    {
        return view('task_statuses.edit', compact('taskStatus'));
    }

    public function update(Request $request, TaskStatus $taskStatus)
    {
        $validated = $request->validate([
            'name' => 'required|unique:task_statuses,name,' . $taskStatus->id . '|min:1|max:255',
        ]);

        $taskStatus->update($validated);

        flash()->success('Статус успешно изменён');

        return redirect()->route('task_statuses.index');
    }

    public function destroy(TaskStatus $taskStatus)
    {
        // Проверяем, есть ли задачи с этим статусом
        if ($taskStatus->tasks()->count() > 0) {
            flash()->error('Не удалось удалить статус');
            return redirect()->route('task_statuses.index');
        }

        $taskStatus->delete();

        flash()->success('Статус успешно удалён');

        return redirect()->route('task_statuses.index');
    }
}
