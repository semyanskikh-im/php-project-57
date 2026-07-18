<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    /**
     * Тест: неавторизованный пользователь может видеть список задач
     */
    public function test_guest_can_view_tasks()
    {
        $response = $this->get(route('tasks.index'));
        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
    }

    /**
     * Тест: неавторизованный пользователь может видеть конкретную задачу
     */
    public function test_guest_can_view_task()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $response = $this->get(route('tasks.show', $task));
        $response->assertStatus(200);
        $response->assertViewIs('tasks.show');
        $response->assertViewHas('task', $task);
    }

    /**
     * Тест: неавторизованный пользователь НЕ может видеть форму создания
     */
    public function test_guest_cannot_view_create_form()
    {
        $response = $this->get(route('tasks.create'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Тест: неавторизованный пользователь НЕ может создать задачу
     */
    public function test_guest_cannot_store_task()
    {
        $status = TaskStatus::factory()->create();

        $taskData = Task::factory()
            ->make(['status_id' => $status->id])
            ->toArray();
        unset($taskData['created_by_id']);

        $response = $this->post(route('tasks.store'), $taskData);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('tasks', ['name' => $taskData['name']]);
    }

    /**
     * Тест: неавторизованный пользователь НЕ может видеть форму редактирования
     */
    public function test_guest_cannot_view_edit_form()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $response = $this->get(route('tasks.edit', $task));
        $response->assertRedirect(route('login'));
    }

    /**
     * Тест: неавторизованный пользователь НЕ может обновить задачу
     */
    public function test_guest_cannot_update_task()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $updatedData = Task::factory()
            ->make(['status_id' => $status->id])
            ->toArray();
        unset($updatedData['created_by_id']);

        $response = $this->put(route('tasks.update', $task), $updatedData);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('tasks', ['name' => $updatedData['name']]);
    }

    /**
     * Тест: неавторизованный пользователь НЕ может удалить задачу
     */
    public function test_guest_cannot_delete_task()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $response = $this->delete(route('tasks.destroy', $task));
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    /**
     * Тест: авторизованный пользователь может видеть список задач
     */
    public function test_authenticated_user_can_view_tasks()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));
        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
    }

    /**
     * Тест: авторизованный пользователь может видеть форму создания
     */
    public function test_authenticated_user_can_view_create_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.create'));
        $response->assertStatus(200);
        $response->assertViewIs('tasks.create');
    }

    /**
     * Тест: авторизованный пользователь может создать задачу
     */
    public function test_authenticated_user_can_create_task()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create(['name' => 'новая']);

        $taskData = Task::factory()
            ->make([
                'status_id' => $status->id,
                'assigned_to_id' => $user->id,
            ])
            ->toArray();
        unset($taskData['created_by_id']);

        $response = $this->actingAs($user)->post(route('tasks.store'), $taskData);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'name' => $taskData['name'],
            'created_by_id' => $user->id,
        ]);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Тест: задача требует название
     */
    public function test_task_requires_name()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'name' => '',
            'status_id' => $status->id,
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Тест: задача требует статус
     */
    public function test_task_requires_status()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'name' => 'Новая задача',
            'status_id' => '',
        ]);

        $response->assertSessionHasErrors('status_id');
    }

    /**
     * Тест: авторизованный пользователь может видеть форму редактирования
     */
    public function test_authenticated_user_can_view_edit_form()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($user)->get(route('tasks.edit', $task));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.edit');
        $response->assertViewHas('task', $task);
    }

    /**
     * Тест: автор задачи может обновить задачу
     */
    public function test_author_can_update_task()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
            'name' => 'Старое название',
        ]);

        $updatedData = Task::factory()
            ->make(['status_id' => $status->id])
            ->toArray();
        unset($updatedData['created_by_id']);

        $response = $this->actingAs($user)->put(route('tasks.update', $task), $updatedData);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['name' => $updatedData['name']]);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Тест: НЕ автор может обновить задачу (любой залогиненный может редактировать)
     */
    public function test_non_author_can_update_task()
    {
        $author = User::factory()->create();
        $anotherUser = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $author->id,
            'status_id' => $status->id,
            'name' => 'Старое название',
        ]);

        $updatedData = Task::factory()
            ->make(['status_id' => $status->id])
            ->toArray();
        unset($updatedData['created_by_id']);

        $response = $this->actingAs($anotherUser)->put(route('tasks.update', $task), $updatedData);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['name' => $updatedData['name']]);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Тест: автор задачи может удалить задачу
     */
    public function test_author_can_delete_task()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Тест: НЕ автор не может удалить задачу
     */
    public function test_non_author_cannot_delete_task()
    {
        $author = User::factory()->create();
        $anotherUser = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $author->id,
            'status_id' => $status->id,
        ]);

        $response = $this->actingAs($anotherUser)->delete(route('tasks.destroy', $task));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
