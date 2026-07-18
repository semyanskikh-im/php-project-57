<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Tests\TestCase;

class TaskStatusControllerTest extends TestCase
{
    /**
     * Гость может видеть список статусов
     */
    public function test_index()
    {
        // Создаём несколько статусов
        TaskStatus::create(['name' => 'Новая']);
        TaskStatus::create(['name' => 'В работе']);

        $response = $this->get(route('task_statuses.index'));

        $response->assertStatus(200);
        $response->assertViewIs('task_statuses.index');
        $response->assertSee('Новая');
        $response->assertSee('В работе');
    }

    /**
     * Гость не может видеть форму создания
     */
    public function test_guest_cannot_create()
    {
        $response = $this->get(route('task_statuses.create'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Гость не может создать статус
     */
    public function test_guest_cannot_store()
    {
        $statusData = TaskStatus::factory()
            ->make()
            ->toArray();

        $response = $this->post(route('task_statuses.store'), $statusData);

        $response->assertRedirect(route('login'));

        // Проверяем, что статус не появился в базе
        $this->assertDatabaseMissing('task_statuses', ['name' => $statusData['name']]);
    }

    /**
     * Авторизованный пользователь может видеть форму создания
     */
    public function test_create()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('task_statuses.create'));
        $response->assertStatus(200);
        $response->assertViewIs('task_statuses.create');
    }

    /**
     * Авторизованный пользователь может создать статус
     */
    public function test_store()
    {
        $user = User::factory()->create();

        $statusData = TaskStatus::factory()
            ->make()
            ->toArray();

        $response = $this->actingAs($user)->post(route('task_statuses.store'), $statusData);

        // Проверяем редирект на список статусов
        $response->assertRedirect(route('task_statuses.index'));

        // Проверяем, что статус появился в базе
        $this->assertDatabaseHas('task_statuses', ['name' => $statusData['name']]);

        // Проверяем флеш-сообщение
        //$response->assertSessionHas('flash_notification');
    }

    /**
     * Создание статуса требует имя
     */
    public function test_store_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('task_statuses.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Имя статуса должно быть уникальным
     */
    public function test_store_name_must_be_unique()
    {
        $user = User::factory()->create();

        // Создаём первый статус
        TaskStatus::create(['name' => 'Новая']);

        // Пытаемся создать статус с таким же именем
        $response = $this->actingAs($user)->post(route('task_statuses.store'), [
            'name' => 'Новая',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Авторизованный пользователь может видеть форму редактирования
     */
    public function test_edit()
    {
        $user = User::factory()->create();
        $status = TaskStatus::create(['name' => 'Новая']);

        $response = $this->actingAs($user)->get(route('task_statuses.edit', $status));

        $response->assertStatus(200);
        $response->assertViewIs('task_statuses.edit');
        $response->assertViewHas('taskStatus', $status);
    }

    /**
     * Авторизованный пользователь может обновить статус
     */
    public function test_update()
    {
        $user = User::factory()->create();
        $status = TaskStatus::create(['name' => 'Новая']);

        $updatedData = TaskStatus::factory()
            ->make(['name' => 'В работе'])
            ->toArray();

        $response = $this->actingAs($user)->put(route('task_statuses.update', $status), $updatedData);

        $response->assertRedirect(route('task_statuses.index'));
        $this->assertDatabaseHas('task_statuses', ['name' => $updatedData['name']]);
        $this->assertDatabaseMissing('task_statuses', ['name' => 'Новая']);

        $response->assertSessionHas('flash_notification');
    }

    /**
     * Авторизованный пользователь может удалить статус (если нет связанных задач)
     */
    public function test_destroy()
    {
        $user = User::factory()->create();
        $status = TaskStatus::create(['name' => 'Новая']);

        $response = $this->actingAs($user)->delete(route('task_statuses.destroy', $status));

        $response->assertRedirect(route('task_statuses.index'));
        $this->assertDatabaseMissing('task_statuses', ['id' => $status->id]);

        $response->assertSessionHas('flash_notification');
    }

    /**
     * Нельзя удалить статус, если с ним связаны задачи
     */
    public function test_destroy_with_tasks()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create(['name' => 'Новая']);

        // Создаём задачу, связанную со статусом
        $task = Task::factory()->create([
            'name' => 'Тестовая задача',
            'status_id' => $status->id,
            'created_by_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('task_statuses.destroy', $status));

        // Проверяем редирект на список статусов
        $response->assertRedirect(route('task_statuses.index'));

        // Проверяем флеш-сообщение об ошибке
        $response->assertSessionHas('flash_notification');

        // Проверяем, что статус не удалился
        $this->assertDatabaseHas('task_statuses', ['id' => $status->id]);
    }
}
