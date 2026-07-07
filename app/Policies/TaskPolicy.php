<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Определяет, может ли пользователь редактировать задачу.
     * Любой авторизованный пользователь может редактировать любую задачу.
     */
    public function update(User $user, Task $task): bool
    {
        return true;
    }

    /**
     * Определяет, может ли пользователь удалять задачу.
     * Только автор может удалять свою задачу.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->created_by_id;
    }
}
