<?php

namespace Tests\Feature;

use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Task;
use Tests\TestCase;

class TaskStatusControllerTest extends TestCase
{
    /**
     * Тест: незалогиненный пользователь может видеть список статусов
     */
    public function test_guest_can_view_statuses()
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
     * Тест: незалогиненный пользователь НЕ может видеть форму создания
     */
    public function test_guest_cannot_view_create_form()
    {
        $response = $this->get(route('task_statuses.create'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Тест: незалогиненный пользователь не может отправить запрос на создание статуса
     */
    public function test_guest_cannot_store_status()
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
     * Тест: только залогиненный пользователь может видеть форму создания статуса
     */
    public function test_authenticated_user_can_view_create_status_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('task_statuses.create'));
        $response->assertStatus(200);
        $response->assertViewIs('task_statuses.create');
    }

    /**
     * Тест: только залогиненный пользователь может создать статус
     */
    public function test_authenticated_user_can_create_status()
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
     * Тест: статус не может быть создан без имени
     */
    public function test_status_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('task_statuses.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Тест: имя статуса должно быть уникальным
     */
    public function test_status_name_must_be_unique()
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
     * Тест: только залогиненный пользователь может видеть форму редактирования статуса
     */
    public function test_authenticated_user_can_view_edit_status_form()
    {
        $user = User::factory()->create();
        $status = TaskStatus::create(['name' => 'Новая']);

        $response = $this->actingAs($user)->get(route('task_statuses.edit', $status));

        $response->assertStatus(200);
        $response->assertViewIs('task_statuses.edit');
        $response->assertViewHas('taskStatus', $status);
    }

    /**
     * Тест: только залогиненный пользователь может обновить статус
     */
    public function test_authenticated_user_can_update_status()
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
     * Тест: только залогиненный пользователь может удалить статус (если нет связанных задач)
     */
    public function test_authenticated_user_can_delete_status_without_tasks()
    {
        $user = User::factory()->create();
        $status = TaskStatus::create(['name' => 'Новая']);

        $response = $this->actingAs($user)->delete(route('task_statuses.destroy', $status));

        $response->assertRedirect(route('task_statuses.index'));
        $this->assertDatabaseMissing('task_statuses', ['id' => $status->id]);

        $response->assertSessionHas('flash_notification');
    }

    /**
     * Тест: НЕЛЬЗЯ удалить статус, если с ним связаны задачи
     */
    public function test_cannot_delete_status_with_tasks()
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
