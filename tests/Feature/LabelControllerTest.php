<?php

namespace Tests\Feature;

use App\Models\Label;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Tests\TestCase;

class LabelControllerTest extends TestCase
{
    /**
     * Гость может видеть список меток
     */
    public function test_index()
    {
        Label::factory()->create(['name' => 'ошибка']);
        Label::factory()->create(['name' => 'фича']);

        $response = $this->get(route('labels.index'));
        $response->assertStatus(200);
        $response->assertViewIs('labels.index');
        $response->assertSee('ошибка');
        $response->assertSee('фича');
    }

    /**
     * Гость не может видеть форму создания
     */
    public function test_guest_cannot_create()
    {
        $response = $this->get(route('labels.create'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Гость не может создать метку
     */
    public function test_guest_cannot_store()
    {
        $labelData = Label::factory()
            ->make()
            ->toArray();

        $response = $this->post(route('labels.store'), $labelData);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('labels', ['name' => $labelData['name']]);
    }

    /**
     * Гость не может видеть форму редактирования
     */
    public function test_guest_cannot_edit()
    {
        $label = Label::factory()->create();

        $response = $this->get(route('labels.edit', $label));
        $response->assertRedirect(route('login'));
    }

    /**
     * Гость не может обновить метку
     */
    public function test_guest_cannot_update()
    {
        $label = Label::factory()->create(['name' => 'Старое имя']);

        $updatedData = Label::factory()
            ->make(['name' => 'Новое имя'])
            ->toArray();

        $response = $this->put(route('labels.update', $label), $updatedData);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('labels', ['name' => 'Старое имя']);
        $this->assertDatabaseMissing('labels', ['name' => $updatedData['name']]);
    }

    /**
     * Гость не может удалить метку
     */
    public function test_guest_cannot_destroy()
    {
        $label = Label::factory()->create();

        $response = $this->delete(route('labels.destroy', $label));
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('labels', ['id' => $label->id]);
    }

    /**
     * Авторизованный пользователь может видеть список меток
     */
    public function test_index_authenticated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('labels.index'));
        $response->assertStatus(200);
        $response->assertViewIs('labels.index');
    }

    /**
     * Авторизованный пользователь может видеть форму создания
     */
    public function test_create()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('labels.create'));
        $response->assertStatus(200);
        $response->assertViewIs('labels.create');
    }

    /**
     * Авторизованный пользователь может создать метку
     */
    public function test_store()
    {
        $user = User::factory()->create();

        $labelData = Label::factory()
            ->make()
            ->toArray();

        $response = $this->actingAs($user)->post(route('labels.store'), $labelData);

        $response->assertRedirect(route('labels.index'));
        $this->assertDatabaseHas('labels', ['name' => $labelData['name']]);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Создание метки требует название
     */
    public function test_store_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('labels.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Имя метки должно быть уникальным
     */
    public function test_store_name_must_be_unique()
    {
        $user = User::factory()->create();

        Label::create(['name' => 'ошибка']);

        $response = $this->actingAs($user)->post(route('labels.store'), [
            'name' => 'ошибка',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Авторизованный пользователь может видеть форму редактирования
     */
    public function test_edit()
    {
        $user = User::factory()->create();
        $label = Label::factory()->create();

        $response = $this->actingAs($user)->get(route('labels.edit', $label));

        $response->assertStatus(200);
        $response->assertViewIs('labels.edit');
        $response->assertViewHas('label', $label);
    }

    /**
     * Авторизованный пользователь может обновить метку
     */
    public function test_update()
    {
        $user = User::factory()->create();
        $label = Label::factory()->create(['name' => 'Старое имя']);

        $updatedData = Label::factory()
            ->make(['name' => 'Новое имя'])
            ->toArray();

        $response = $this->actingAs($user)->put(route('labels.update', $label), $updatedData);

        $response->assertRedirect(route('labels.index'));
        $this->assertDatabaseHas('labels', ['name' => $updatedData['name']]);
        $this->assertDatabaseMissing('labels', ['name' => 'Старое имя']);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Авторизованный пользователь может удалить метку (без связанных задач)
     */
    public function test_destroy()
    {
        $user = User::factory()->create();
        $label = Label::factory()->create();

        $response = $this->actingAs($user)->delete(route('labels.destroy', $label));

        $response->assertRedirect(route('labels.index'));
        $this->assertDatabaseMissing('labels', ['id' => $label->id]);
        $response->assertSessionHas('flash_notification');
    }

    /**
     * Нельзя удалить метку, если она связана с задачей
     */
    public function test_destroy_with_tasks()
    {
        $user = User::factory()->create();
        $status = TaskStatus::factory()->create();
        $label = Label::factory()->create();
        $task = Task::factory()->create([
            'created_by_id' => $user->id,
            'status_id' => $status->id,
        ]);

        // Привязываем метку к задаче (многие ко многим)
        $task->labels()->attach($label->id);

        $response = $this->actingAs($user)->delete(route('labels.destroy', $label));

        $response->assertRedirect(route('labels.index'));
        $response->assertSessionHas('flash_notification');
        $this->assertDatabaseHas('labels', ['id' => $label->id]);
    }
}
