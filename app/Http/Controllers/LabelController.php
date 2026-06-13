<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    /**
     * Отображает список всех меток.
     * Доступно всем пользователям (включая неавторизованных)
     */
    public function index()
    {
        $labels = Label::paginate(15);
        return view('labels.index', compact('labels'));
    }

    /**
     * Отображает форму создания новой метки.
     * Только для авторизованных пользователей
     */
    public function create()
    {
        return view('labels.create');
    }

    /**
     * Сохраняет новую метку в базе данных.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:labels|min:1|max:255',
            'description' => 'nullable|string',
        ]);

        Label::create($validated);

        flash()->success('Метка успешно создана');

        return redirect()->route('labels.index');
    }

    /**
     * Отображает конкретную метку.
     * Доступно всем пользователям
     */
    public function show($id)
    {
        $label = Label::findOrFail($id);
        return view('labels.show', compact('label'));
    }

    /**
     * Отображает форму редактирования метки.
     * Только для авторизованных пользователей
     */
    public function edit($id)
    {
        $label = Label::findOrFail($id);
        return view('labels.edit', compact('label'));
    }

    /**
     * Обновляет метку в базе данных.
     */
    public function update(Request $request, $id)
    {
        $label = Label::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|unique:labels,name,' . $label->id . '|min:1|max:255',
            'description' => 'nullable|string',
        ]);

        $label->update($validated);

        flash()->success('Метка успешно изменена');

        return redirect()->route('labels.index');
    }

    /**
     * Удаляет метку из базы данных.
     * Если метка связана с какой-либо задачей — удаление запрещено.
     */
    public function destroy($id)
    {
        $label = Label::findOrFail($id);

        // Проверяем, есть ли задачи с этой меткой
        if ($label->tasks()->count() > 0) {
            flash()->error('Не удалось удалить метку');
            return redirect()->route('labels.index');
        }

        $label->delete();

        flash()->success('Метка успешно удалена');

        return redirect()->route('labels.index');
    }
}
