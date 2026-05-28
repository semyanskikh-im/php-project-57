<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->post(route('tasks.store'), [
            'name' => 'Тестовая задача',
            'status_id' => $status->id,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('tasks', ['name' => 'Тестовая задача']);
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

        $response = $this->put(route('tasks.update', $task), [
            'name' => 'Обновлённая задача',
            'status_id' => $status->id,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('tasks', ['name' => 'Обновлённая задача']);
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

        $taskData = [
            'name' => 'Новая задача',
            'description' => 'Описание задачи',
            'status_id' => $status->id,
            'assigned_to_id' => $user->id,
        ];

        $response = $this->actingAs($user)->post(route('tasks.store'), $taskData);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'name' => 'Новая задача',
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

        $updatedData = [
            'name' => 'Новое название',
            'description' => 'Новое описание',
            'status_id' => $status->id,
        ];

        $response = $this->actingAs($user)->put(route('tasks.update', $task), $updatedData);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['name' => 'Новое название']);
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

        $response = $this->actingAs($anotherUser)->put(route('tasks.update', $task), [
            'name' => 'Обновлено не автором',
            'status_id' => $status->id,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['name' => 'Обновлено не автором']);
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
