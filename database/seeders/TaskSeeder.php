<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Label;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = TaskStatus::all();
        $users = User::all();
        $labels = Label::all();

        if ($statuses->isEmpty() || $users->isEmpty()) {
            return;
        }

        $tasks = [
            [
                'name' => 'Устроить вечеринку в Диснейлэнде',
                'description' => 'Организовать незабываемую вечеринку для друзей',
            ],
            [
                'name' => 'Поздравить Винни-Пуха с днём рождения',
                'description' => 'Подготовить поздравление и подарок',
            ],
            [
                'name' => 'Погулять  в парке',
                'description' => 'Собрать друзей на площадке',
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = Task::create([
                'name' => $taskData['name'],
                'description' => $taskData['description'],
                'status_id' => $statuses->random()->id,
                'created_by_id' => $users->random()->id,
                'assigned_to_id' => $users->random()->id,
            ]);

            if ($labels->isNotEmpty()) {
                $task->labels()->attach($labels->random(rand(1, min(2, $labels->count())))->pluck('id'));
            }
        }
    }
}
