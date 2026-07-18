<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    /**
     * Гость может видеть список задач
     */
    public function test_index()
    {
        $response = $this->get(route('tasks.index'));
        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
    }

    /**
     * Гость может видеть конкретную задачу
     */
    public function test_show()
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
     * Гость не может видеть форму создания
     */
    public function test_guest_cannot_create()
    {
        $response = $this->get(route('tasks.create'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Гость не может создать задачу
     */
    public function test_guest_cannot_store()
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
     * Гость не может видеть форму редактирования
     */
    public function test_guest_cannot_edit()
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
     * Гость не может обновить задачу
     */
    public function test_guest_cannot_update()
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
     * Гость не может удалить задачу
     */
    public function test_guest_cannot_destroy()
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
     * Авторизованный пользователь может видеть список задач
     */
    public function test_index_authenticated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));
        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
    }

    /**
     * Авторизованный пользователь может видеть форму создания
     */
    public function test_create()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.create'));
        $response->assertStatus(200);
        $response->assertViewIs('tasks.create');
    }

    /**
     * Авторизованный пользователь может создать задачу
     */
    public function test_store()
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
     * Создание задачи требует название
     */
    public function test_store_requires_name()
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
     * Создание задачи требует статус
     */
    public function test_store_requires_status()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'name' => 'Новая задача',
            'status_id' => '',
        ]);

        $response->assertSessionHasErrors('status_id');
    }

    /**
     * Авторизованный пользователь может видеть форму редактирования
     */
    public function test_edit()
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
     * Автор задачи может обновить задачу
     */
    public function test_update()
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
     * Не автор может обновить задачу (любой авторизованный может редактировать)
     */
    public function test_update_by_non_author()
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
     * Автор задачи может удалить задачу
     */
    public function test_destroy()
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
     * Не автор не может удалить задачу
     */
    public function test_destroy_by_non_author()
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
